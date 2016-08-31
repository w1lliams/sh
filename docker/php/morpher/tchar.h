#pragma once

#ifdef _WIN32
	#include <tchar.h>
#endif

#ifndef TCHAR
	#ifdef _UNICODE
		#define TCHAR wchar_t
	#else
		#define TCHAR char
	#endif
#endif

#ifndef _T
	#ifdef _UNICODE
		#define _T(x) L ## x
	#else
		#define _T(x) x
	#endif
#endif
