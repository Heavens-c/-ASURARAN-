#pragma comment(linker, "/EXPORT:ijlGetLibVersion=_ijlGetLibVersion@0")
#pragma comment(linker, "/EXPORT:ijlInit=_ijlInit@4")
#pragma comment(linker, "/EXPORT:ijlFree=_ijlFree@4")
#pragma comment(linker, "/EXPORT:ijlRead=_ijlRead@8")
#pragma comment(linker, "/EXPORT:ijlWrite=_ijlWrite@8")
#pragma comment(linker, "/EXPORT:ijlErrorStr=_ijlErrorStr@4")

int __stdcall ijlGetLibVersion(void) { return 0; }
int __stdcall ijlInit(void* a) { return 0; }
int __stdcall ijlFree(void* a) { return 0; }
int __stdcall ijlRead(void* a, int b) { return 0; }
int __stdcall ijlWrite(void* a, int b) { return 0; }
const char* __stdcall ijlErrorStr(int a) { return "OK"; }

int __stdcall DllMain(void* hinst, unsigned long reason, void* reserved) {
    return 1;
}
