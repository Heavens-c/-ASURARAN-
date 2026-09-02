# Security Audit & Malware/Backdoor Sweep Findings

**Target Codebase**: RanOnline MMO Server & Client Suite (`[ASURARAN]`)  
**Audit Date**: September 2026  
**Auditor**: Antigravity Security Agent  
**Branch**: `audit-hardening`

---

## Executive Summary
A comprehensive security sweep of the entire codebase (ServerLogin, ServerSession, ServerAgent, ServerField, GameClient2, Lib_Network, Lib_Client, Lib_Engine, Lib_Helper, Lib_ZLib, and tooling) was conducted. The codebase contains standard RanOnline EP7/EP8 source architecture modified with community features (e.g. `dmk14` in-game web/top-up systems, activity systems, item locking).

Several critical vulnerabilities and architectural security issues were identified:
1. **Critical: Remote Command Injection & Stack Buffer Overflow in IP Blocker** (`WinExec` + `netsh`)
2. **High: Unauthenticated Server-Side Event & Reward Trigger** (`ServerSlotEventGen`)
3. **High: Inventory & Trade Concurrency Duplication Vectors** (Missing trade-state checks in inventory mutation handlers)
4. **Medium: Hardcoded Default Credentials & Legacy Server Endpoints** (`wItemPass`, default fallback IPs)
5. **Low: Unbounded Array Loop in Result Processing** (Pandora box results)

---

## Detailed Findings

### Finding 1: Remote Command Injection & Stack Buffer Overflow in Firewall Rule Spawning
- **Files**:
  - `Lib_Network/s_CClientManager.cpp:1318`
  - `Lib_Network/s_CIPFilter.cpp:150`
- **Component**: `CClientManager::IPAddClient()` & `CIPFilter::Add()`
- **Severity**: **CRITICAL**
- **Description**:
  When an IP reaches maximum connections or triggers a bad packet filter, the server attempts to execute Windows Firewall rules via `WinExec`:
  ```cpp
  char jblock[125];
  sprintf(jblock,"netsh advfirewall firewall add rule name=\"%s\" dir=in interface=any action=block remoteip=%s/32", _strIP.c_str(), _strIP.c_str() );
  WinExec(jblock, SW_HIDE);
  ```
- **Vulnerability Breakdown**:
  1. **Stack Buffer Overflow**: `jblock` is fixed at 125 bytes. The template string alone is 89 bytes long. If `_strIP` is formatted twice into `name` and `remoteip`, any input longer than 17 characters will overflow the stack buffer.
  2. **Command Injection**: `_strIP` originates from socket peer addressing and string conversions. If unvetted string content reaches this formatter, shell metacharacters (`&`, `|`, `;`) would execute arbitrary system commands under the server's Windows account privileges.
  3. **Process Denial of Service**: Under connection flood attacks, spawning a synchronous OS process (`netsh.exe`) for each blocked connection starves server threads and crashes the process.
- **Remediation**:
  Remove `WinExec` and the `jblock` buffer entirely. The engine already maintains in-memory blocking tables (`m_setIPBlock`, `m_mapClientIP_BLOCK`) and socket-level drop routines in `s_CIPFilter`.

---

### Finding 2: Unauthenticated Server-Side GM / Admin Event Packet Trigger
- **Files**:
  - `Lib_Client/G-Logic/GLGaeaServerMsg.cpp:7410` (`GLGaeaServer::ServerSlotEventGen`)
  - `Lib_Client/dxincommand.cpp:164`
- **Component**: `GLGaeaServer` Message Dispatch
- **Severity**: **HIGH**
- **Description**:
  The client chat parser (`dxincommand.cpp`) exposes `/slotbegin` to generate global slot reward events. While the client checks `m_dwUserLvl < USER_MASTER`, the receiving Field Server handler (`GLGaeaServerMsg.cpp:7410`) does not verify whether the requesting character possesses GM privileges before initiating the event and setting `m_sItemSlotReward = SNATIVEID(pNetMsg->dwMid, pNetMsg->dwSid)`.
- **Impact**:
  A player with a modified client or packet injector can send a raw `NET_MSG_GCTRL_SLOT_EVENT_GEN_FLD` packet to spawn arbitrary server-wide slot rewards.
- **Remediation**:
  Add server-side sender validation and authorization checks before executing `ServerSlotEventGen`.

---

### Finding 3: Item Duplication Vector via Trade & Inventory State Race Conditions
- **Files**:
  - `Lib_Client/G-Logic/GLCharInvenMsg.cpp:1591` (`MsgReqInvenToHold`)
  - `Lib_Client/G-Logic/GLCharInvenMsg.cpp:4068` (`MsgReqInvenSplit`)
  - `Lib_Client/G-Logic/GLCharactorReq.cpp:1000-1630`
- **Component**: `GLChar` Inventory & Trade State Machines
- **Severity**: **HIGH**
- **Description**:
  Client-side trade validations were commented out in `GLCharactorReq.cpp` (`//if ( GLTradeClient::GetInstance().Valid() ) return E_FAIL;`). On the Field server (`GLCharInvenMsg.cpp`), several inventory mutation handlers (such as item hold, item split, and slot equip) lacked checks for `m_sTrade.Valid()`.
- **Impact**:
  A malicious client can initiate a trade transaction, place items into trade escrow, and simultaneously send rapid `NET_MSG_GCTRL_REQ_INVEN_TO_HOLD` or `NET_MSG_GCTRL_REQ_INVEN_SPLIT` packets to mutate the inventory during the trade confirmation window, leading to item duplication.
- **Remediation**:
  Ensure all inventory mutation message handlers on `GLChar` strictly verify that the character is not engaged in an active trade (`if ( m_sTrade.Valid() || m_bClubStorage || m_bMarketOpen ) return S_FALSE;`).

---

### Finding 4: Default Hardcoded Passcodes & Legacy Connection Fallbacks
- **Files**:
  - `Lib_Client/G-Logic/GLogicData.cpp:723` (`wItemPass = 101496`)
  - `Lib_Engine/Utils/RANPARAM.cpp:144` (`LoginAddress = "211.172.252.50"`)
- **Component**: Global Constants and Default Configuration
- **Severity**: **MEDIUM**
- **Description**:
  - `wItemPass` is assigned a default static value `101496` in `GLogicData.cpp`. If server administrators do not override `wItemPass` in server configuration scripts (`logic.ini` / `.glf`), unauthorized GM item generation could succeed with the known default passcode.
  - `RANPARAM.cpp` contains hardcoded legacy test IPs (`211.172.252.50`, `211.224.129.220`).
- **Remediation**:
  Ensure production deployment guides require rotating `wItemPass` and configuring explicit server endpoints in client and server configuration files.

---

### Finding 5: Missing Bounds Check in Pandora Box Item Result Packing
- **Files**:
  - `Lib_Client/G-Logic/GLCharMsg.cpp:3680-3690`
- **Component**: `GLChar::MsgProcess` (`NET_MSG_PANDORA_BOX_REFRESH_RESULT`)
- **Severity**: **LOW**
- **Description**:
  The result iterator unpacks items into a fixed-size buffer array `NetMsgRes.sBOX[i++] = iter->second;` without verifying `i < sizeof(NetMsgRes.sBOX)/sizeof(NetMsgRes.sBOX[0])`.
- **Remediation**:
  Add defensive boundary checks on array indices.

---

## Secrets and Credential Rotation Checklist
> [!IMPORTANT]
> The following credentials and configuration parameters must be rotated in production environments:
> - **Game Master Item Passcode (`wItemPass`)**: Rotate from default `101496` in server configuration (`param.ini` / `glogic.ini`).
> - **Database ODBC Credentials**: Rotate SQL Server `RanUser`, `RanGame`, `RanLog`, `RanShop` passwords in `ServerLogin.ini`, `ServerSession.ini`, `ServerAgent.ini`, `ServerField.ini`.
> - **TEA Encryption Keys**: Ensure TEA keys in `s_NetGlobal.h` / `minTea.h` match across trusted binaries and are not published in client builds without binary protection.
