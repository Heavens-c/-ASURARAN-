# Security Policy & Audit Summary

## Audit & Hardening Summary

During the security review of the RanOnline codebase, an in-depth audit across network packet dispatchers, persistent storage routines, and memory buffers was conducted.

### Identified & Remediated Items
1. **Removed Unsafe OS Command Spawning in IP Blocker** (`Lib_Network/s_CClientManager.cpp:1318`, `Lib_Network/s_CIPFilter.cpp:150`):
   - **Vulnerability**: Execution of `WinExec(jblock, SW_HIDE)` formatted with `netsh advfirewall` using an undersized 125-byte buffer `jblock`.
   - **Remediation**: Replaced with safe in-memory table tracking and console logging without invoking external processes.
2. **Hardened Trade Concurrency & Anti-Duplication Controls** (`Lib_Client/G-Logic/GLCharInvenMsg.cpp:1615`, `Lib_Client/G-Logic/GLCharInvenMsg.cpp:3100`):
   - **Vulnerability**: Inventory manipulation handlers lacked checks for active trade escrow state (`m_sTrade.Valid()`).
   - **Remediation**: Gated all inventory hold and item split operations behind trade lock verification (`if ( m_sTrade.Valid() || m_bClubStorage ) return S_FALSE;`).
3. **Hardened Server-Side Event Item Spawning** (`Lib_Client/G-Logic/GLGaeaServerMsg.cpp:7410`):
   - **Vulnerability**: `ServerSlotEventGen` accepted event payloads without item validation.
   - **Remediation**: Added item existence validation before assigning global reward state.
4. **Defensive Array Bounds Protection** (`Lib_Client/G-Logic/GLCharMsg.cpp:3688`):
   - **Vulnerability**: Missing loop bounds verification when copying Pandora box results into fixed-size message array.
   - **Remediation**: Added explicit array length check before assignment.

---

## Security Checklist for Future Pull Requests

Before merging any pull request to the game server codebase, ensure compliance with the following security checklist:

- [ ] **No Client-Authoritative Stats or Economy Writes**:
  - Are character level, gold, and stat points calculated and validated solely on the server?
  - Are client packets treated as unverified requests rather than authoritative state?
- [ ] **No In-Band Shell Execution**:
  - Does the PR avoid using `WinExec`, `system()`, `ShellExecute`, or `CreateProcess` on data derived from network packets?
- [ ] **Trade & Escrow State Isolation**:
  - Do any new inventory or storage operations check `if ( m_sTrade.Valid() || m_bClubStorage || m_bMarketOpen ) return S_FALSE;`?
- [ ] **Server-Side GM Permission Checks**:
  - Do all administrative packet handlers verify `pChar->m_dwUserLvl >= USER_GM3` on the receiving server daemon?
- [ ] **Input Sanitization & Buffer Safety**:
  - Are string inputs checked with `STRUTIL::CheckString()` before being passed into database queries?
  - Are all message buffer copies protected by `StringCchCopy` or explicit length checks?
- [ ] **Credential Protection**:
  - Are passwords and secrets encrypted with TEA/DES before transmission over socket connections?
  - Are default passcodes (`wItemPass`) rotated in configuration files for production deployments?
