# CLAUDE.md

## Overview & Workflow
This repository contains the full source code for the **RanOnline MMORPG** distributed server cluster, 3D client (`GameClient2`), shared libraries (`Lib_Engine`, `Lib_Network`, `Lib_Client`), and development editors.

---

## Toolchain & Build Specifications
- **Build System**: Microsoft Visual Studio MSBuild (`RanOnline.sln`, `.vcxproj`)
- **Toolset**: `v143` (Visual Studio 2022)
- **Target Platform**: `Win32` (x86 32-bit architecture)
- **C/C++ Runtime**: Dynamic MFC (`/MD` for Release, `/MDd` for Debug)
- **Character Set**: Multi-Byte (`MBCS`)

### Build Commands
```powershell
# Build entire server suite (Release Win32)
msbuild RanOnline.sln /p:Configuration=Release /p:Platform=Win32 /m

# Build individual server projects
msbuild ServerLogin/ServerLogin.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild ServerSession/ServerSession.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild ServerAgent/ServerAgent.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild ServerField/ServerField.vcxproj /p:Configuration=Release /p:Platform=Win32
msbuild GameClient2/GameClient2.vcxproj /p:Configuration=Release /p:Platform=Win32
```

---

## Code Structure & Conventions
- `Lib_Engine/`: Render engine (DirectX 9 SDK), mathematical utilities, Octree spatial indexing, sound engine.
- `Lib_Network/`: IOCP sockets (`s_CServer.cpp`), packet encryption (`minTea.cpp`, `des.cpp`, `MinLzo.cpp`), ODBC persistence (`s_COdbcManager.cpp`, `s_CDbAction.cpp`).
- `Lib_Client/`: Core gameplay logic (`GLChar.cpp`, `GLItemMan.cpp`, `GLSkill.cpp`, `GLGaeaServer.cpp`, `GLAgentServer.cpp`).
- `Lib_ClientUI/`: In-game GUI dialogs, HUD widgets, item tooltip rendering.
- `Tik/`: Vendored third-party dependencies (`DirectX 9`, `Intel TBB`, `Ogg/Vorbis`, `Boost`, `Lua`).

---

## Developer & Claude Code Security Rules
- **Server Authoritative Validation**:
  - Never trust client packets for currency (`m_lnMoney`), character level (`m_wLevel`), or stat values (`m_wStatsPoint`).
  - Positional movements must validate spatial distance caps (`GLCharMsg.cpp:270`).
- **No In-Band Shell Execution**:
  - Do not use `WinExec`, `system()`, or `CreateProcess` for network packet handling or IP filtering (`s_CClientManager.cpp:1318`, `s_CIPFilter.cpp:150`).
- **Transactional State Management**:
  - Prevent trade / storage race conditions: check `if ( m_sTrade.Valid() || m_bClubStorage ) return S_FALSE;` before any inventory mutation (`GLCharInvenMsg.cpp:1615`).
- **No Unvetted Admin Handlers**:
  - Ensure every GM packet verifies `pChar->m_dwUserLvl >= USER_GM3` server-side (`GLGaeaServerMsg.cpp:7410`, `GLAgentServerMsg.cpp:3400`).
- **Database Safety**:
  - Sanitize all strings against SQL metacharacters using `STRUTIL::CheckString()` (`Lib_Engine/Common/StringUtils.cpp:214`).
