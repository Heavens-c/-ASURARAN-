# Subsystem: Persistence Layer & Database Engine

## Purpose
The Persistence subsystem manages database connections, asynchronous query job queues, binary blob serialization (inventory, skills, quests), and transaction execution across the SQL databases.

---

## Key Files & Classes

| File | Component / Class | Responsibility |
| :--- | :--- | :--- |
| `Lib_Network/s_COdbcManager.h/.cpp` | `COdbcManager` | ODBC environment, connection pool, query execution methods. |
| `Lib_Network/s_COdbcEnv.h/.cpp` | `COdbcEnv` | ODBC Core SQLHANDLE allocation, statement handles. |
| `Lib_Network/s_CDbAction.h/.cpp` | `CDbAction` / `CDbActionQueue` | Background thread worker pool for non-blocking asynchronous SQL tasks. |
| `Lib_Network/s_COdbcUser.cpp` | User DB Queries | Account auth, password verification, registration, bans (`RanUser`). |
| `Lib_Network/s_COdbcGame.cpp` | Game DB Queries | Character lists, slot allocation, guild/club queries (`RanGame`). |
| `Lib_Network/s_COdbcGameChaSave.cpp` | `SaveChaInfo` | Serializes character stats, binary blobs for Inven, Skills, Quests. |
| `Lib_Network/s_COdbcLog.cpp` | Logging DB Queries | Audit logs for trades, GM actions, item generation (`RanLog`). |

---

## Database Schemas & Roles

### 1. `RanUser` Database
- **`UserInfo` Table**: `UserNum` (PK), `UserId`, `UserPass`, `UserType` (Account Level / GM), `UserBlock`, `UserLoginState`, `UserAge`.
- **Purpose**: Stores account authentication credentials, security PIN codes, and access permissions.

### 2. `RanGame` Database
- **`ChaInfo` Table**: `ChaNum` (PK), `UserNum` (FK), `ChaName`, `ChaClass`, `ChaLevel`, `ChaMoney`, `ChaHP`, `ChaMP`, `ChaSP`, `ChaCP`, `ChaInven` (VARBINARY), `ChaPutOnItems` (VARBINARY), `ChaSkills` (VARBINARY), `ChaQuest` (VARBINARY), `GuNum` (Guild ID).
- **`GuildInfo` Table**: `GuNum` (PK), `GuName`, `ChaNum` (Master ID), `GuMoney`, `GuStorage` (VARBINARY), `GuNotice`, `GuMarkVer`.
- **`PetInfo` / `VehicleInfo` Tables**: Companion data, stats, items.

### 3. `RanLog` Database
- **`LogItem` / `LogAction` Tables**: Records item creations, drop pickups, trade exchanges, and admin GM commands with timestamps.

---

## Asynchronous Database Execution Pipeline

```text
Game Tick Thread (GLChar)
        │
        ▼ (Enqueues DB Task without blocking game tick)
COdbcManager::GetInstance()->AddGameJob( new CDbActionGameChaSave(...) )
        │
        ▼
CDbActionQueue (FIFO Task Queue)
        │
        ▼ (Dequeued by Background Worker Thread)
DbActionThread -> COdbcManager::SaveChaInfo() -> Execute SQL Stored Procedure
        │
        ▼
ODBC Driver -> Microsoft SQL Server
```

---

## Database Security & Injection Prevention

- **Strict Input Validation**: All user-supplied text parameters (account names, passwords, character names, search strings) are filtered through `STRUTIL::CheckString()` (`Lib_Engine/Common/StringUtils.cpp:214`).
- **Binary Parameter Binding**: Binary blobs (`ChaInven`, `ChaSkills`, `ChaQuest`) are bound using `SQLBindParameter` and `SQL_C_BINARY` rather than formatted as text literals.
