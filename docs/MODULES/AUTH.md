# Subsystem: Authentication & Session Management

## Purpose
The Authentication subsystem handles client credential verification, encryption, account privileges, anti-bruteforce/IP filtering, double-login prevention, and session ticket issuance.

---

## Key Files & Classes

| File | Component / Class | Responsibility |
| :--- | :--- | :--- |
| `ServerLogin/ServerLogin.cpp` | `WinMain`, `MainDlgProc` | Entry point, GUI control, launcher/game version display. |
| `Lib_Network/s_CLoginServer.h/.cpp` | `CLoginServer` | Login network daemon, client socket manager, packet router. |
| `Lib_Network/s_CLoginServerMsg.cpp` | `CLoginServer::MsgProcess` | Dispatches `NET_MSG_LOGIN_REQ`, `NET_MSG_VERSION_CHECK`. |
| `Lib_Network/s_COdbcUser.cpp` | `COdbcManager::UserCheck` | Executes `sp_CheckUser` against `RanUser..UserInfo`. |
| `Lib_Network/s_CSessionServer.h/.cpp` | `CSessionServer` | Single-login tracking table, inter-server ticket coordination. |
| `Lib_Network/s_CIPFilter.cpp` | `CIPFilter` | IP address ban list, connection limits per IP (`m_setIPBlock`). |

---

## Authentication Flow

1. **Client Connection & Version Check**:
   - Client sends `NET_MSG_VERSION_CHECK` (`s_NetGlobal.h:400`).
   - Server validates launcher and game binary version numbers against `CLoginServer::GetGameVersion()`.
2. **Credential Decryption & Verification**:
   - Client encrypts user ID and password using TEA (`minTea.cpp:40`).
   - Server receives `NET_MSG_LOGIN_REQ` in `s_CLoginServerMsg.cpp:45`, decrypts payload with `m_Tea.decrypt()`.
   - Performs string validation with `STRUTIL::CheckString()` (`StringUtils.cpp:214`) to block SQL metacharacters.
3. **Database Stored Procedure**:
   - Enqueues `CDbActionUserCheck` to ODBC worker thread (`s_CDbActionUser.cpp:30`).
   - Database validates password hash, account status (active/banned/expired), and returns `dwUserLvl` (0 = Normal, 1 = GM1, 2 = GM2, 3 = GM3, 4 = Master).
4. **Session Coordinator Registration**:
   - `CLoginServer` queries `ServerSession` (`s_CSessionServerMsg.cpp:110`) to confirm the account is not actively logged in on another node.
   - Issues a temporary cryptographic session key (`dwRandomKey`).
5. **Server List Dispatch**:
   - Server returns `NET_MSG_LOGIN_FB` containing server group names, channel counts, and Agent Server IP/Port endpoints.
   - Client disconnects from `ServerLogin` and transitions to `ServerAgent`.
