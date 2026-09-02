# Build Guide

## Prerequisites & Toolchain Requirements

- **Operating System**: Windows 10 / Windows 11 / Windows Server 2019+
- **Compiler**: Visual Studio 2022 (MSVC Toolset `v143`)
- **Required Workloads**:
  - Desktop development with C++
  - C++ MFC for latest v143 build tools (x86 & x64)
  - Windows 10 SDK (`10.0.19041.0` or later)
- **DirectX 9 SDK**: Bundled in `Tik/DXInclude` and `Tik/DXLib`
- **Intel TBB**: Bundled in `Tik/TbbLib`

---

## Build Targets & Output Paths

| Project | Solution Folder | Configuration | Output Path |
| :--- | :--- | :--- | :--- |
| `Lib_BugTrap` | `Lib` | Release / Win32 | `_Bin/Data/BugTrap.dll` |
| `Lib_ZLib` | `Lib` | Release / Win32 | `_Build/Release/Lib_ZLib/Lib_ZLib.lib` |
| `Lib_Helper` | `Lib` | Release / Win32 | `_Build/Release/Lib_Helper/Lib_Helper.lib` |
| `Lib_Engine` | `Lib` | Release / Win32 | `_Build/Release/Lib_Engine/Lib_Engine.lib` |
| `Lib_Network` | `Lib` | Release / Win32 | `_Build/Release/Lib_Network/Lib_Network.lib` |
| `Lib_Client` | `Lib` | Release / Win32 | `_Build/Release/Lib_Client/Lib_Client.lib` |
| `Lib_ClientUI` | `Lib` | Release / Win32 | `_Build/Release/Lib_ClientUI/Lib_ClientUI.lib` |
| `ServerLogin` | `Servers` | Release / Win32 | `_Bin/Tool/ServerLogin.exe` |
| `ServerSession` | `Servers` | Release / Win32 | `_Bin/Tool/ServerSession.exe` |
| `ServerAgent` | `Servers` | Release / Win32 | `_Bin/Tool/ServerAgent.exe` |
| `ServerField` | `Servers` | Release / Win32 | `_Bin/Tool/ServerField.exe` |
| `GameClient2` | `Client` | Release / Win32 | `_Bin/Data/GameClient.exe` |
| `GameEmulator` | `Client` | Release / Win32 | `_Bin/Data/GameEmulator.exe` |

---

## Step-by-Step Build Instructions

### Method 1: Command Line (MSBuild)
Open **Developer Command Prompt for VS 2022** or PowerShell with MSBuild on PATH:

```powershell
# 1. Navigate to workspace root
Set-Location -LiteralPath "c:\Users\dhudz\Desktop\[ASURARAN]"

# 2. Build Core Libraries First (Dependency Order)
msbuild Lib_ZLib/Lib_ZLib.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild Lib_Helper/Lib_Helper.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild Lib_Engine/Lib_Engine.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild Lib_Network/Lib_Network.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild Lib_Client/Lib_Client.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild Lib_ClientUI/Lib_ClientUI.vcxproj /p:Configuration=Release /p:Platform=Win32

# 3. Build Server Daemons
msbuild ServerLogin/ServerLogin.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild ServerSession/ServerSession.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild ServerAgent/ServerAgent.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild ServerField/ServerField.vcxproj /p:Configuration=Release /p:Platform=Win32

# 4. Build Client Executables
msbuild GameClient2/GameClient2.vcxproj /p:Configuration=Release /p:Platform=Win32
```

### Method 2: Visual Studio IDE
1. Open `RanOnline.sln` in Visual Studio 2022.
2. Set configuration to `Release` and platform to `Win32`.
3. Right-click the Solution and select **Build Solution** (`Ctrl+Shift+B`).

---

## Platform Quirks & Compiler Flags

- **Multi-Byte Character Set (MBCS)**: The codebase requires MFC Multi-Byte character support. If missing, install via Visual Studio Installer under "C++ MFC for v143 build tools (x86 & x64)".
- **Legacy Standard I/O Definitions**: Projects link `legacy_stdio_definitions.lib` to maintain compatibility with legacy DirectX 9 and C runtime format functions (`sprintf`, `sscanf`).
- **Precompiled Headers**: All projects use `stdafx.h` / `stdafx.cpp` precompiled header files for build acceleration.
