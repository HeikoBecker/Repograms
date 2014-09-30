#!/usr/bin/env python2
# -!- encoding: utf-8

from __future__ import print_function

import subprocess
# import itertools
import heapq
import json

from functools import wraps

import graph_tool as gt
import graph_tool.topology

def debug_on(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        global debug
        old = debug
        def debug(*args, **kwargs):
            print(*args, file=sys.stderr, **kwargs)
        result = func(*args, **kwargs)
        debug = old
        return result
    return wrapper

def debug(*args, **kwargs):
    pass


class Order:
    TOPO = 1
    CHRONO = 2


class PriorityQueue:
    """A priority queue. Returs elements with lower priority first"""
    def __init__(self):
        self._queue = []
        self._index = 0

    def push(self, item, priority):
        heapq.heappush(self._queue, (priority, self._index, item))
        self._index += 1

    def pop(self):
        return heapq.heappop(self._queue)[-1]


def get_commit_data(commit_id):
    # iterate over commits
    # --always: handle empty commits
    # -s: don't show p, we do it later
    # --root: else the first commit won't work
    # -r: the commit we want to display
    # --pretty: format string which prints all we need
    #   %P: parents   |   %ct: commiter time stamp     | %B commitmsg
    separator = "\a"
    command = """git diff-tree --always --root --no-commit-id --numstat --pretty=format:{1}%P{1}%ct{1}%B{1} -r {0}""".format(commit_id, separator)
    pipe = subprocess.Popen(command.split(" "), stdout=subprocess.PIPE)
    out, err = pipe.communicate()
    filestats, parents, commit_timestamp, commitmsg, _ = out.decode('utf8', 'ignore').split(separator)
    lines_added = 0
    lines_removed = 0
    file_names = []
    for file_info in filestats.split("\n"):
        if file_info:
            added, removed, name = file_info.split("\t")
            try:
                lines_added += int(added)
                lines_removed += int(removed)
            except ValueError:
                pass  # binary files don't have those numbers
            # TODO: deal with renames!
            file_names.append(name)
    return (parents.split(), commit_timestamp, commitmsg,
            lines_added, lines_removed, file_names)


def get_all_commits():
    command = """git rev-list --all --remotes --reverse --topo-order"""
    pipe = subprocess.Popen(command.split(" "), stdout=subprocess.PIPE)
    out, err = pipe.communicate()
    all_commits = out.decode('utf8', 'ignore').split("\n")[:-1]
    return all_commits


class GitGraph():

    def __init__(self):
        self.graph = gt.Graph()
        # required to map a nodes hashsum back to the vertex
        self.hash2vertex = {}

        # properties associated with each commit
        self.commit_hashsum = self.graph.new_vertex_property("string")
        self.commit_msg = self.graph.new_vertex_property("object")
        self.commit_timestamp = self.graph.new_vertex_property("string")
        self.commit_files = self.graph.new_vertex_property("vector<string>")
        self.commit_churn = self.graph.new_vertex_property("int")
        self.branch_complexity = self.graph.new_vertex_property("int")

        # sentinel: required to have a rooted DAG
        self.sentinel = self.graph.add_vertex()
        self.commit_hashsum[self.sentinel] = "SENTINEL"
        self.hash2vertex["SENTINEL"] = self.sentinel

        # construct the graph
        for commit in get_all_commits():
            parents, commit_timestamp, commitmsg, added, removed, files = get_commit_data(commit)
            commit_vertex = self.graph.add_vertex()
            self.hash2vertex[commit] = commit_vertex
            self.commit_hashsum[commit_vertex] = commit
            self.commit_msg[commit_vertex] = commitmsg
            self.commit_timestamp[commit_vertex] = commit_timestamp
            self.commit_files[commit_vertex] = files
            self.commit_churn[commit_vertex] = added+removed
            self.branch_complexity[commit_vertex] = 0
            if not parents:
                debug("initial commit detected: {}".format(commit))
                self.graph.add_edge(self.sentinel, commit_vertex)
                continue
            for parent in parents:
                debug("adding edge from {} to {}".format(parent, commit))
                self.graph.add_edge(self.hash2vertex[parent], commit_vertex)
        self.transitive_closure = gt.topology.transitive_closure(self.graph)
        # make closure also reflexive
        for vertex in self.transitive_closure.vertices():
            self.transitive_closure.add_edge(vertex, vertex)
        assert gt.topology.is_DAG(self.graph)

        # compute dominators
        self.dominator_tree = self.compute_dominators()

    def compute_dominators(self):
        """
        Compute the dominator set from the dominator tree
        """
        domtree = gt.topology.dominator_tree(self.graph, self.sentinel)
        return domtree

    def _created_branches_count(self, commit_node, children):
        """
        Returns the number of new branches a commit creates
        Consider
        A---B---D--F---...
        \        /
         \--C---E---G---...
        here E will have two children, F and G
        However, it doesn't create a new branch, because it was already
        created by A
        To fix this, we increase the counter
        ifF the commit dominates it children
        """
        if not len(children) > 1:
            return 0
        branch_counter = 0
        for child in children:
            if commit_node == self.graph.vertex(self.dominator_tree[child]):
                branch_counter += 1
        branch_counter -= 1  # one child is from the "main" branch
        # There are actually commits with multiple children which dominate none
        # of them, in this case branch_counter becomes negative because of the
        # last line, adjusting for the "main" branch; therefore we have to reset
        # branch_counter to zero
        # An example of such a commit is 738036395d68824933949186603aeeb9c087d10e
        # of the repograms repository
        branch_counter = max(branch_counter, 0)
        assert branch_counter >= 0, "A negative number of branches cannot exist: branch_counter: {}, #children: {}, commit: {}".format(branch_counter, len(children), commit_node)
        if __debug__:
            if branch_counter != 0:
                debug("started:", self.commit_hashsum[commit_node])
            else:
                debug("not started:", self.commit_hashsum[commit_node])
        return branch_counter


    @debug_on
    def _ended_branches_count(self, commit_node, parents):
        if not len(parents) > 1:
            return 0
        """
        Consider
        A------C--D--F (master)
          \    /
            \--B-----E  (feature branch)
        In this case, C has multiple parents
        However, it is NOT the end of a branch, as the feature
        branch is still continued (by commit E)
        To respect this, we only substract one from the counter for
        each parent with only one child
        """
        ended_counter = 0
        for parent in parents:
            # TODO: not in parents is not sufficiant
            # we need to check if is there is a path from child to parents -
            # parent
            destinies = set(parents)
            destinies.remove(parent)
            parent_counter = 0
            for child in parent.out_neighbours():
                if child == commit_node:
                    continue
                for destiny in destinies:
                    path_exists = self.transitive_closure.edge(child, destiny) is not None
                    if path_exists:
                        parent_counter += 1
            child_count = sum(1 for _ in parent.out_neighbours())
            if child_count - parent_counter  == 1:
                # commit_node is the last commit of the branch
                ended_counter += 1
        ended_counter -= 1  # one parent is from the "main" branch
        # A merge commit might not end any branch, e.g. commit
        # 9b0235dd7ca88fa1f5a83552b457bf95b6de6f73
        # of the bootstrap repository
        # Therefore we set the counter to 0 if it's negative
        ended_counter = max(ended_counter, 0)
        assert ended_counter >= 0, "commit cannot end negative number of branches: branch_counter: {}, #parents: {}, commit: {}".format(ended_counter, len(parents), self.commit_hashsum[commit_node])
        if __debug__:
            if ended_counter != 0:
                debug("ended:", self.commit_hashsum[commit_node], "number: ", ended_counter)
            else:
                debug("not ended:", self.commit_hashsum[commit_node])
                debug("parents are")
                for parent in parents:
                    debug("\t",self.commit_hashsum[parent])
                    for child in parent.out_neighbours():
                        debug("\t\tchild: ", self.commit_hashsum[child])
                        debug("")
        return ended_counter


    def metric6(self):
        branch_counter = 0
        for commit_node in self.iterate_commits(Order.TOPO):
            # iterate over commits in order of commit_timestamps
            debug(self.commit_hashsum[commit_node])
            parents = list(commit_node.in_neighbours())
            children = list(commit_node.out_neighbours())
            if parents[0] == self.sentinel:  # first commit of branch
                assert(len(parents) == 1), "First commit of branch has no predecessor"
                branch_counter += 1
            branch_counter -= self._ended_branches_count(commit_node, parents)
            assert branch_counter >= 1, "There should be at least one branch all the time: branch_counter: {}, commit {}: ".format(branch_counter, self.commit_hashsum[commit_node])
            self.branch_complexity[commit_node] = branch_counter
            # add the newly created branches AFTERWARDS
            # the branches diverge at this commit, but the number of branches
            # is only increased in the children
            branch_counter += self._created_branches_count(commit_node,
                                                           children)
            assert branch_counter >= 1,"There should be at least one branch all the time: branch_counter: {}, commit {}: ".format(branch_counter, self.commit_hashsum[commit_node])
        # visited all nodes


    def iterate_commits(self, order=Order.CHRONO):
        if order == Order.TOPO:
            # topological_sort: if edge (u,v) appears in the graph, then v
            # comes before u in the ordering
            # we want however the reverse ordering (which is what people in
            # general understand by topological order)
            for commit_index in reversed(gt.topology.topological_sort(self.graph)):
                commit_node = self.graph.vertex(commit_index)
                debug("Current element", self.commit_hashsum[commit_node])
                if (commit_node == self.sentinel):
                    # we got the sentinel node, which is not a real commit node
                    continue
                yield commit_node
        elif order == Order.CHRONO:
            unvisited_nodes = PriorityQueue()
            already_seen = set()
            for initial_commit in self.sentinel.out_neighbours():
                unvisited_nodes.push(initial_commit, self.commit_timestamp[initial_commit])
                already_seen.add(initial_commit)
            while(True):
                # iterate over commits in order of commit_timestamps
                try:
                    commit_node = unvisited_nodes.pop()
                except IndexError:
                    raise StopIteration
                yield commit_node
                children = commit_node.out_neighbours()
                new_nodes = [child for child in children if child not in already_seen]
                for node in new_nodes:
                    unvisited_nodes.push(node, self.commit_timestamp[node])
                already_seen |= set(new_nodes)

    def export_as_json(self):
        result = []
        for commit in self.iterate_commits():
            result.append({
                "churn": self.commit_churn[commit],
                "commitmsg": self.commit_msg[commit],
                "files": list(self.commit_files[commit]),
                "bcomplexity": self.branch_complexity[commit],
            })
        return json.dumps(result, separators=(',', ':'))


if __name__ == "__main__":
    import sys
    import os
    import tempfile
    if len(sys.argv) < 1:
        print("missing argument")
        sys.exit(0)
    dirpath = tempfile.mkdtemp()
    os.chdir(dirpath)
    command = "git clone {} .".format(sys.argv[1])
    subprocess.check_call(command.split())
    g = GitGraph()
    g.metric6()
    exported = g.export_as_json()
    print(exported)
