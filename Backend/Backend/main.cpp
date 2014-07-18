// Backend.cpp: определяет точку входа для консольного приложения.
//

#include "stdafx.h"
#include "Analyzer.h"


int main(int argc, char * argv[])
{
	using namespace std;
	string str = "10.0.2.2 - - [13/Jul/2014:21:51:33 +0700] \"GET /about-us/ HTTP/1.0\" 200 341 \"http://site1.test.plesk.ru:8890/\" \"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36/\""; 
	//cin >> str;
	TLogRecord  logRecord;
	Analyzer anal;
	anal.parseString(str,logRecord);
	return 0;
}

