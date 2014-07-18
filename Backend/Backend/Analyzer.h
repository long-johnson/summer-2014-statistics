#pragma once

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
	long int bytes;		// размер объекта, возращенного клиенту (байты)
	std::string referer;		// URL, на который поступил запрос
	std::string client;		// имя браузера клиента
};

class Analyzer
{
private:
	int hitsInHour[24];			// количество запросов за час
	std::string currentDate;	// обрабатываемая дата

public:
	Analyzer(void);
	~Analyzer(void);

	// проанализировать свежие логи, переместить обработанные в файл processedLogFileName, 
	// сохранить статистику в uniqueVisitorsFileName
	bool analyzeLogFile(const std::string logFileName, const std::string processedLogFileName, 
						const std::string uniqueVisitorsFileName, const std::string hitsFileName);
	// разобрать строку
	bool parseString(std::string str, TLogRecord & logRecord);
};

