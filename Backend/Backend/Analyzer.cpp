#include "stdafx.h"
#include "Analyzer.h"


Analyzer::Analyzer(void)
{
}


Analyzer::~Analyzer(void)
{
}

bool Analyzer::analyzeLogFile(const std::string logFileName, const std::string processedLogFileName, 
					const std::string uniqueVisitorsFileName, const std::string hitsFileName)
{
	using namespace std;
	// откроем логфайл
	ifstream logFile(logFileName);
	if (!logFile.is_open())
	{
		std::cerr << "<ExStatistics> Can't open logFile " + logFileName << endl;
		return false;
	}

	// считаем весь логфайл в массив строк
	std::vector<std::string> stringsToAnalyze;
	while (!logFile.eof())
	{
		std::string temp;
		getline(logFile,temp);
		stringsToAnalyze.push_back(temp);
	}
	logFile.close();

	// очистим логфайл
	{ofstream of; of.open(logFileName,std::ios_base::trunc);}

	// запишем обработанные логи в конец файла обработанных логов
	ofstream processedLogFile(processedLogFileName,std::ios::app);
	if (!processedLogFile.is_open()){
		std::cerr << "<ExStatistics> Access to processedLogFile " + logFileName + " is forbidden" << endl;
		// TODO: записать логи обратно в logFileName, чтобы не потерялись
	} else
		for(auto it = stringsToAnalyze.begin() ; it != stringsToAnalyze.end(); ++it)
		{
			processedLogFile << *it << endl;
		}
	currentDate = "";
	// обработаем необработанные логи
	for(auto it = stringsToAnalyze.begin() ; it != stringsToAnalyze.end(); ++it)
	{
		TLogRecord record;
		bool ok = parseString(*it,record);	// разбор строки (превращение её в запись)
		// если удалось разобрать (и поле даты не пусто), обработаем запись
		if (ok && record.date != "")
		{
			// если обрабатывается следующий день, то обнулим счетчики
			if (currentDate != record.date)
			{
				currentDate = record.date;
				for(int i=0;i<24;i++) hitsInHour[i]=0;
			}
			hitsInHour[record.hour]++;		// увеличим число обращений в этот час на 1
		}
	}

	// HITS
	// добавим статистику о текущей дате в конец файла
	ofstream hitsFile(hitsFileName,std::ios::app);
	if (hitsFile.is_open())
		std::cerr << "<ExStatistics> Access to hitsFile " + hitsFileName+ " is forbidden" << endl;
	// запишем дату, а затем информацию о показателях по часам
	hitsFile << 
}

const std::string LOG_NOT_AVAILIABLE = "-";

bool Analyzer::parseString(std::string str, TLogRecord & logRecord)
{
	using namespace std;
	string temp;
	char ctemp;
	stringstream sstream(str);
	
	// считаем ip
	sstream >> temp;
	if (temp != LOG_NOT_AVAILIABLE)
		logRecord.origin = temp;
	else
		logRecord.origin = "";

	// считаем identd
	sstream >> temp;
	if (temp != LOG_NOT_AVAILIABLE)
		logRecord.identd = temp;
	else
		logRecord.identd = "";

	// считаем auth
	sstream >> temp;
	if (temp != LOG_NOT_AVAILIABLE)
		logRecord.auth = temp;
	else
		logRecord.auth = "";

	// считаем датувремя
	//sstream >> ctemp;		// пробел
	sstream >> ctemp;		// - или кавычка
	temp = ctemp;
	if (temp != LOG_NOT_AVAILIABLE)
	{
		sstream >> temp;		// строка дата + время
		size_t pos1 = temp.find_first_of(':');
		logRecord.date = temp.substr(0,pos1);	// строка с датой
		size_t pos2 = temp.find_first_of(pos1+1,':');	
		logRecord.hour = std::stoi(temp.substr(pos1+1,pos2-1));	// час
		sstream >> temp;	// считаем окончание строки
	}
	else{
		logRecord.date = ""; logRecord.hour = "";
	}

	// считаем метод, ресурс, протокол
	//sstream >> ctemp;		// пробел
	sstream >> ctemp;		// - или кавычка
	temp = ctemp;
	if (temp != LOG_NOT_AVAILIABLE)
	{
		sstream >> logRecord.method;
		sstream >> logRecord.path;
		sstream >> logRecord.protocol;  
		logRecord.protocol.pop_back(); // уберем "
	}else
	{
		logRecord.method="";
		logRecord.path = "";
		logRecord.protocol = "";
	}

	// считаем код состояния
	sstream >> temp;
	if (temp != LOG_NOT_AVAILIABLE)
	{
		logRecord.status = temp;
	} else
		logRecord.status = "";

	// считаем количество байтов
	logRecord.bytes = 0;
	sstream >> logRecord.bytes;
	if (logRecord.bytes == 0)
		sstream >> temp;

	// считаем адрес URL, оставив только относительный путь
	sstream >> temp;
	if (temp != LOG_NOT_AVAILIABLE)
	{
		temp.pop_back(); temp.erase(0,1); // уберем кавычки спереди и сзади
		// уберем http(s)://имя_хоста
		size_t pos = temp.find_first_of('/');
		pos = temp.find_first_of(pos+1,'/');
		pos = temp.find_first_of(pos+1,'/');
		temp.erase(0,pos);
		logRecord.referer = temp;
	}else
		logRecord.referer = "";

	// считаем информацию о браузере
	sstream >> ctemp;	// пробел
	sstream >> ctemp;	// - или "
	temp = ctemp;
	if (temp != LOG_NOT_AVAILIABLE)
	{
		//size_t pos = sstream.str().find_first_of('\"');
		//logRecord.client = sstream.str().substr(0,pos);
		sstream >> std::noskipws;
		logRecord.client.clear();
		do{
			logRecord.client += ctemp;
			sstream >> ctemp;
		}while(ctemp !='\"');
		sstream >> std::skipws;
	}
	else
		logRecord.client = "";
	
	return true;
}