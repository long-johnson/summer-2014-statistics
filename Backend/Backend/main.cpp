// Backend.cpp: определяет точку входа для консольного приложения.
//

//#include "stdafx.h"
#include "Analyzer.h"
#include <stdio.h>
#include <string>
#include <fstream>
#include <iostream>
#include <vector>
#include <sstream>
#include <set>
#include <map>


int main(int argc, char * argv[])
{
	using namespace std;
	// считаем список подписок
	ifstream subscriptionsFile("subscriptions.txt");
	if (!subscriptionsFile.is_open()) {
		std::cerr << "<ExStatistics> Can't open subscriptions.txt" << endl;
		return false;
	}
	std::vector<std::string> subscriptions;
	while (!subscriptionsFile.eof())
	{
		std::string temp;
		subscriptionsFile >> temp; //getline(subscriptionsFile,temp);
		if (!temp.empty())
			subscriptions.push_back(temp);
	}
	subscriptionsFile.close();

	// заполним список сайтов, которые нужно обработать и путей к ним
	std::vector<std::string> pathToStatFiles;
	std::vector<std::string> pathToLogFiles;
	for (int i=0, size=subscriptions.size(); i<size; i++){
		string str = subscriptions[i];
		ifstream sitesFile(str + "/sites.txt");
		if (!sitesFile.is_open()) {
			std::cerr << "<ExStatistics> Can't open " + str + "/sites.txt" << endl;
			return false;
		}
		while (!sitesFile.eof())
		{
			std::string temp;
			sitesFile >> temp; //getline(sitesFile,temp);
			if (!temp.empty()){
				pathToStatFiles.push_back("/usr/local/psa/var/modules/extended-plesk-statistics/" + str + "/"+ temp + "/");
				pathToLogFiles.push_back("/var/www/vhosts/system/" + temp + "/logs/");
			}
		}
		sitesFile.close();
	}

	// запустим анализ логов
	Analyzer anal;
	for (int i=0, size = pathToStatFiles.size(); i < size; i++){
		string acces_log = pathToLogFiles[i] + "access_log";
		string acces_log_processed = pathToLogFiles[i] + "access_log.processed";
		string unique =  pathToStatFiles[i] + "unique_visitors.stat";
		string hits =  pathToStatFiles[i] + "hits.stat";
		string band =  pathToStatFiles[i] + "bandwidth.stat";
		string pages =  pathToStatFiles[i] + "pages.stat";
		string visits =  pathToStatFiles[i] + "visits.stat";
		string ip_mapping =  pathToStatFiles[i] + "ip_mapping.stat";
		anal.analyzeLogFile(acces_log,acces_log_processed,unique,hits,band,pages,visits,ip_mapping);
	}
	return 0;
}

