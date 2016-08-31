#pragma once

namespace std
{

#ifdef _UNICODE
	#define tstring wstring
	#define tostringstream wostringstream
#else
	#define tstring string
	#define tostringstream ostringstream
#endif

}
