// Backend.cpp: определяет точку входа для консольного приложения.
//

#include "stdafx.h"
#include "Analyzer.h"


int main(int argc, char * argv[])
{
	TLogRecord  logRecord;
	Analyzer anal;
	anal.analyzeLogFile("access.log","access.log","unique_visitors.stat","hits.stat","bandwidth.stat","pages.stat","visits.stat","ip_mapping.stat");
	return 0;
}

