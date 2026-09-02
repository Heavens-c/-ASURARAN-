# AGENTS.md

## Project Purpose
RanOnline MMO is a persistent-world, multi-server 3D MMORPG server and client suite written in C++ (Visual Studio MSBuild toolchain, MFC/Win32 GUI, DirectX 9). The architecture distributes game state across specialized server daemons coordinating via TCP IPC, IOCP socket pools, and ODBC database connections.

---

## Service & Binary Matrix

| Service / Binary | Project File | Output Executable | Primary Responsibility |
| :--- | :--- | :--- | :--- |
| **ServerLogin** | `ServerLogin/ServerLogin.vcxproj` | `_Bin/Tool/ServerLogin.exe` | Account authentication, password hashing/TEA, IP filter, patch verification, server list gateway. |
| **ServerSession** | `ServerSession/ServerSession.vcxproj` | `_Bin/Tool/ServerSession.exe` | Cluster state bus, single-login coordinator, channel balance, cross-server IPC router. |
| **ServerAgent** | `ServerAgent/ServerAgent.vcxproj` | `_Bin/Tool/ServerAgent.exe` | Character lobby (create/delete/select), party/club/guild manager, whisper/shout/chat router, zone handoff gateway. |
| **ServerField** | `ServerField/ServerField.vcxproj` | `_Bin/Tool/ServerField.exe` | Zone engine (`GLGaeaServer`), map ticking, combat/physics/collision, NPC/mob AI (`GLCrow`), trade/inventory, instance PvP. |
| **GameClient2** | `GameClient2/GameClient2.vcxproj` | `_Bin/Data/GameClient.exe` | Primary 3D client game application (DirectX 9, UI engine, input/rendering). |
| **GameEmulator** | `GameEmulator/GameEmulator.vcxproj` | `_Bin/Data/GameEmulator.exe` | Local development client simulator and rapid test harness. |
| **GMTool** | `GMTool/GMTool.vcxproj` | `_Bin/Tool/GMTool.exe` | Game Master / Administrator operational console and character editor. |

---

## Directory Map

```text
[ASURARAN]/
├── BugTrap/            # Crash reporting, minidump handler, callstack capture
├── CryptionRCC/        # .rcc archive encrypter/decrypter tool
├── EditGenItem/        # Item generator editor tool
├── EditorActivity/     # Activity/achievement data editor
├── EditorCodex/        # Codex collection system editor
├── EditorItem/         # Item definition and parameter editor
├── EditorItemMix/      # Item mix / crafting recipe editor
├── EditorLevel/        # Level / Map structure & spawn editor
├── EditorMapsList/     # World map list configuration tool
├── EditorMobNpc/       # Monster / NPC stats and drops editor
├── EditorNpcAction/    # NPC talk and dialogue script editor
├── EditorQuest/        # Quest logic and step editor
├── EditorSkill/        # Skill tree and combat effect editor
├── EditorSkinChar/     # Character skin mesh/skeleton tool
├── EditorSkinPiece/    # Armor and accessory skin piece tool
├── EditorTaxi/         # Taxi / travel destination node editor
├── EditorText/         # String table and localization editor
├── FileCrypt/          # File encryption / decryption utility
├── GameClient2/        # Client executable entry point and stage management
├── GameEmulator/       # Standalone client simulation testbed
├── GameViewer/         # 3D asset, character, and animation previewer
├── GMTool/             # Game Master admin management console
├── Lib_Client/         # Core game logic (GLChar, GLItem, GLSkill, GLQuest, GLLandMan)
│   ├── G-Logic/        # Gameplay state machines, packet handlers, PvP arenas
│   └── NpcTalk/        # NPC conversation graph definitions
├── Lib_ClientUI/       # Client HUD, widgets, dialog interfaces
├── Lib_Engine/         # Core engine, DirectX 9 render layer, math, octree, audio
│   ├── Common/         # String utilities, memory structures, assertions
│   ├── DxCommon9/      # Direct3D 9 device initialization and vertex pipelines
│   └── Utils/          # Exception handling, logging, registry, configuration
├── Lib_Helper/         # MFC GUI controls, report views, custom sliders
├── Lib_Network/        # IOCP network layer, encryption (TEA, DES, LZO), ODBC persistence
├── Lib_ZLib/           # ZLib compression archive backend
├── res/                # Application icons and GUI resource templates
├── ServerAgent/        # Agent server daemon project
├── ServerField/        # Field/Zone server daemon project
├── ServerLogin/        # Login server daemon project
├── ServerSession/      # Session coordinator server daemon project
├── Tik/                # Vendored dependencies (DirectX 9 SDK, Intel TBB, Ogg/Vorbis, Boost, Lua)
└── docs/               # Architecture, Security, Build, and Subsystem Documentation
```

---

## Build Commands

- **Solution Build (MSBuild / Visual Studio 2022 v143 Toolset)**:
  ```powershell
  # Build entire solution in Release Win32
  msbuild RanOnline.sln /p:Configuration=Release /p:Platform=Win32 /m

  # Build specific server daemon (e.g. ServerField)
  msbuild ServerField/ServerField.vcxproj /p:Configuration=Release /p:Platform=Win32
  ```

---

## Security Rules for AI Agents & Developers

1. **Never Trust Client-Reported Movement or Stats**:
   - Client packets `NET_MSG_GCTRL_GOTO` and `NET_MSG_GCTRL_REQ_STATSUP` only convey requests.
   - Positional delta must remain within tolerance (`fDist <= 60.0f` in `GLCharMsg.cpp:270`).
   - Stat point deductions must be authoritative (`m_wStatsPoint` validation in `GLCharMsg.cpp:1880`).
2. **Never Execute OS Shell Commands or Process Spawns from Network Inputs**:
   - Do NOT use `WinExec`, `system()`, `ShellExecute`, or `CreateProcess` to invoke external utilities (e.g. `netsh.exe`) based on client IP or packet values (`s_CClientManager.cpp:1318`, `s_CIPFilter.cpp:150`).
   - Handle rate limiting and IP blocking using in-memory hash sets (`CIPFilter::m_setIPBlock`).
3. **All Economy & Inventory Operations Must Be Transactional & State-Gated**:
   - Any inventory mutation (item hold, item split, equip, storage transfer) MUST verify `if ( m_sTrade.Valid() || m_bClubStorage || m_bMarketOpen ) return S_FALSE;` (`GLCharInvenMsg.cpp:1615`, `GLCharInvenMsg.cpp:3100`).
   - Gold and trade exchanges must execute atomic double-commit validation.
4. **All Server-Side GM / Admin Handlers Must Enforce Authority**:
   - Never rely on client-side permission checks alone (`dxincommand.cpp`).
   - Every admin packet handler must check `pChar->m_dwUserLvl >= USER_GM3` on the receiving server (`GLGaeaServerMsg.cpp:7410`, `GLAgentServerMsg.cpp:3400`).
5. **Protect Database Query Construction**:
   - User-supplied strings must be passed through `STRUTIL::CheckString` before being used in SQL statements (`Lib_Engine/Common/StringUtils.cpp:214`).
   - Use parameterized queries or stored procedures for persistent writes.
6. **Do Not Modify Vendored `/Tik` or `/BugTrap` Source Directly**:
   - Treat `Tik/` and `BugTrap/` as read-only upstream dependencies.
