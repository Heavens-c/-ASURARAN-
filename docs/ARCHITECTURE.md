# System Architecture

## Distributed Multi-Server Topology

RanOnline MMO employs a distributed multi-process server architecture designed to scale across multiple CPU cores and network nodes.

```mermaid
flowchart TD
    Client["GameClient2 / GameEmulator (DirectX 9)"]

    subgraph Cluster ["Server Cluster"]
        Login["ServerLogin (Port: 5001)\n- User Authentication\n- Version Verification\n- Server/Channel Discovery"]
        Session["ServerSession (Port: 5002)\n- Cluster State Coordinator\n- Single-Login Verification\n- Server Heartbeat Sync"]
        Agent["ServerAgent (Port: 5101+)\n- Character Selection & Lobby\n- Party & Guild (Club) Bus\n- Global & Whisper Chat\n- Field Handoff Gateway"]
        Field1["ServerField (Channel 1, Field 1)\n- Zone Map Ticking (GLGaeaServer)\n- Combat, Skills, Collision\n- Inventory, Trades, NPC/Mob AI"]
        Field2["ServerField (Channel 1, Field 2)\n- Additional Maps / Dungeons\n- Instance PvP (Tyranny, SW, CTF)"]
    end

    subgraph Database ["Persistence Layer (ODBC / MSSQL)"]
        DB_User[("RanUser Database\n- UserInfo / Accounts\n- Passwords / Bans")]
        DB_Game[("RanGame Database\n- ChaInfo / Stats / Level\n- ChaInven / Skills\n- Guild / Pet / Vehicle")]
        DB_Log[("RanLog Database\n- Action Logs\n- Economy / Trade Logs")]
        DB_Shop[("RanShop Database\n- Web Item Shop")]
    end

    Client -->|1. Auth Request| Login
    Login -->|ODBC Auth Query| DB_User
    Login -->|2. Verify Session Ticket| Session
    Login -->|3. Return Server List| Client

    Client -->|4. Connect to Agent| Agent
    Agent <-->|IPC Sync & Status| Session
    Agent -->|ODBC Load Char List| DB_Game

    Client -->|5. Connect to Field (Zone)| Field1
    Client -.->|Zone Transfer| Field2
    Field1 <-->|Field Status & Handoff| Agent
    Field2 <-->|Field Status & Handoff| Agent
    Field1 <-->|IPC Heartbeat| Session
    Field2 <-->|IPC Heartbeat| Session

    Field1 -->|Async DbAction Queue| DB_Game
    Field1 -->|Async DbAction Queue| DB_Log
```

---

## Session Lifecycle State Machine

```mermaid
sequenceDiagram
    autonumber
    actor Player as Player / Client
    participant Login as ServerLogin
    participant Session as ServerSession
    participant Agent as ServerAgent
    participant Field as ServerField
    participant DB as MS SQL DB

    Note over Player,Login: Phase 1: Authentication
    Player->>Login: NET_MSG_LOGIN_REQ (TEA encrypted ID/Pass)
    Login->>DB: sp_CheckUser() / COdbcManager::UserCheck()
    DB-->>Login: User Data & Permissions (dwUserLvl)
    Login->>Session: NET_MSG_LOGIN_SESSION_CHECK (Prevent Double-Login)
    Session-->>Login: Session Confirmed (Unique Session Key)
    Login-->>Player: NET_MSG_LOGIN_FB (Success, Server/Channel List, Session Key)
    Player-xLogin: Disconnect Login Socket

    Note over Player,Agent: Phase 2: Lobby & Character Management
    Player->>Agent: NET_MSG_LOGIN_TO_AGENT (Session Key)
    Agent->>Session: NET_MSG_SESSION_VERIFY
    Session-->>Agent: Token OK
    Agent->>DB: COdbcManager::GetChaInfoList()
    DB-->>Agent: Character Slot Binary Summaries
    Agent-->>Player: NET_MSG_CHAR_LIST
    Player->>Agent: NET_MSG_SELECT_CHAR (ChaNum)
    Agent->>DB: COdbcManager::GetChaInfo() (Full Blobs: Inven, Skills, Quests)
    DB-->>Agent: Full Character Data
    Agent-->>Player: NET_MSG_JOIN_TO_FIELD (Field IP, Field Port, Ticket)

    Note over Player,Field: Phase 3: World Entry & Gameplay Loop
    Player->>Field: NET_MSG_FIELD_ENTER (Ticket)
    Field->>Field: Instantiate GLChar on GLLandMan
    Field-->>Player: NET_MSG_CHAR_ENTER_ZONE (Spawn Self, Nearby Entities, Mobs)
    loop Active World Tick (FrameMove)
        Player->>Field: NET_MSG_GCTRL_GOTO (Target Vector)
        Field->>Field: GLChar::MsgGoto() (Spatial Validation, Collision)
        Field-->>Player: NET_MSG_GCTRL_GOTO_BRD (Broadcast Position)
        Player->>Field: NET_MSG_GCTRL_REQ_SKILL (Skill ID, Target ID)
        Field->>Field: GLChar::MsgReqSkill() (Cooldown, Range, Cost, Damage Calc)
        Field-->>Player: NET_MSG_GCTRL_SKILL_BRD (Apply Damage, Animation)
    end

    Note over Player,Field: Phase 4: Persistence & Logout
    alt Periodic Autosave or Logout
        Field->>DB: AddGameJob(CDbActionGameChaSave)
        DB-->>Field: Save OK
    end
    Player-xField: Disconnect Socket
    Field->>Agent: NET_MSG_CHAR_LEAVE
    Agent->>Session: NET_MSG_SESSION_RELEASE
```

---

## Threading Model

1. **Network I/O Thread Pool (IOCP)**:
   - Managed in `s_CServer.cpp:115` (`WorkerThread`).
   - Uses `CreateIoCompletionPort` and `GetQueuedCompletionStatus` for concurrent packet reads/writes without per-connection threads.
2. **Main Engine Tick Loop**:
   - `GLGaeaServer::FrameMove` (Field Server) and `GLAgentServer::FrameMove` (Agent Server).
   - Runs deterministic game simulation cycles (NPC AI updates, mob spawning schedules, buff decays, PvP state progression).
3. **Asynchronous Database Worker Pool**:
   - Managed in `s_CDbAction.cpp:80` (`CDbActionQueue` / `DbActionThread`).
   - Offloads slow disk I/O and stored procedures (`sp_SaveChaInfo`, logging) to background worker threads, preventing latency spikes on the game tick.
