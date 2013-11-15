<?php
/**
 * Interface for accessing and querying reposa
 */
interface Repo_Interface 
{
	/**
	 * @return an array containing the name of all branches
	 * @throws An exception e; e->getMessage() contains a description of 
	 * what have failed
	 */
	public function ListBranches();

	/**
	 * Changes the current branch to $branch
	 * @throws An exception e; e->getMessage() contains a description of 
	 * what have failed
	 */
	public function SwitchToBranch($branch);

	/**
	 * @return a commit object, representing the first commit
	 * @throws An exception e; e->getMessage() contains a description of 
	 * what have failed
	 */
	public function GetFirstCommit();

	/**
	 * @return an array containing all git objects
	 * @throws An exception e; e->getMessage() contains a description of 
	 * what have failed
	 */
	public function GetAllCommits();

	/**
	 * @return the amount of commits in the repo
	 */
	public function getTotalCommitCount();

}
