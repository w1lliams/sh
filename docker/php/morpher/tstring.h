#pragma once

namespace std
{

#ifdef _UNICODE
	#define tstring wstring
	#define tostringstream wostringstream
	#define tstrcmp wcscmp
#else
	#define tstring string
	#define tostringstream ostringstream
	#define tstrcmp strcmp
#endif

}
