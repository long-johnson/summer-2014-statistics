#pragma once

typedef long int TBandwidth;
typedef long int THits;

// Структура "запись лога" - разобранная строка лога
struct TLogRecord
{
	std::string origin;				// ip клиента
	std::string identd;			// (-)
	std::string auth;			// id клиента (-)
	//struct tm datetime;			// дата, время. (m_isdst : 0 - время не указано, не 0 - указано)
	std::string date;
	int hour;						// час
	std::string method;				// метод запроса
	std::string path;	// имя запрашиваемого ресурса
	std::string protocol;	// название протокола (HTTP/1.0)
	std::string status;				// код состояния
	TBandwidth bytes;		// размер объекта, возращенного клиенту (байты)
	std::string referer;		// URL, на который поступил запрос
	std::string client;		// имя браузера клиента
};

class Analyzer
{
private:
	int hitsInHour[24];					// количество запросов за час
	int uniqIPsInHour[24];				// количество уникальныз IP за час
	TBandwidth bandwidthInHour[24];		// количество отданных байтов за час
	int pagesInHour[24];				// посещ. страницы в час
	int visitsInHour[24];				// посещений в час
	std::string currentDate;	// обрабатываемая дата
	int currentHour;			// обрабатываемый час
	std::set<std::string> setOfUniqueIPs;		// множество айпи, с которых обратились за день
	std::set<std::string> setOfUniqueIPsHour;	// множество айпи, с которых обратились за час
	// Отображения: IP <-> bytes/pages/visits/hits	
	std::map<std::string,TBandwidth> mapIpBandwidth;
	std::map<std::string,int> mapIpPages;
	std::map<std::string,THits>	mapIpHits;
	std::map<std::string,int>	mapIpVisits;


public:
	Analyzer(void);
	~Analyzer(void);
	///
	/// проанализировать свежие логи, переместить обработанные в файл processedLogFileName, 
	/// сохранить статистику в uniqueVisitorsFileName
	///
	bool analyzeLogFile(const std::string logFileName, const std::string processedLogFileName, 
						const std::string uniqueVisitorsFileName, const std::string hitsFileName,
						const std::string bandwidthFileName, const std::string pagesFilename,
						const std::string visitsFileName);
	///
	/// разобрать строку
	///
	bool parseString(std::string str, TLogRecord & logRecord);
};

