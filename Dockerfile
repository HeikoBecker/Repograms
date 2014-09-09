FROM ubuntu:14.04
MAINTAINER Repograms Team <github.com/HeikoBecker/Repograms>
#first install dependencies, python2, networkx for python2, apache2, php and git
RUN apt-get update 
RUN apt-get install -y php5 apache2 git subversion mercurial-git python python-networkx
#copy the project to the www folder
RUN git clone https://github.com/HeikoBecker/Repograms.git /var/www/html
#checkout the gui_minimal branch
RUN cd /var/www/html && git checkout gui_minimal
