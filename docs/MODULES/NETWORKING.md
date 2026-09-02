# Subsystem: Networking, Sockets & Cryptography

## Purpose
The Networking subsystem provides asynchronous high-performance I/O Completion Port (IOCP) socket transport, packet framing, buffer pooling, and cryptographic encryption for server-to-client and server-to-server communications.

---

## Key Files & Classes

| File | Component / Class | Responsibility |
| :--- | :--- | :--- |
| `Lib_Network/s_CServer.h/.cpp` | `CServer` | Base TCP server abstraction, IOCP worker threads, listener socket. |
| `Lib_Network/s_CNetUser.h/.cpp` | `CNetUser` | Connected peer socket session, send/receive buffers, encryption context. |
| `Lib_Network/s_CClientManager.h/.cpp` | `CClientManager` | Connected client slot table, IP connection limiter. |
| `Lib_Network/s_NetGlobal.h/.cpp` | Message Definitions | Opcode enumerations, `NET_MSG_GENERIC` header format. |
| `Lib_Network/minTea.h/.cpp` | `minTea` | Tiny Encryption Algorithm (TEA / XTEA / XXTEA) implementation. |
| `Lib_Network/des.h/.cpp` | `DES` | Data Encryption Standard algorithm implementation. |
| `Lib_Network/MinLzo.h/.cpp` | `MinLzo` | LZO lossless real-time packet data compression engine. |
| `Lib_Network/dhkey.h/.cpp` | `dhkey` | Diffie-Hellman cryptographic key exchange protocol. |

---

## Packet Protocol & Framing

All network messages begin with the `NET_MSG_GENERIC` header (`Lib_Network/s_NetGlobal.h:185`):

```cpp
struct NET_MSG_GENERIC
{
    DWORD dwSize;  // Total size of the packet including header
    DWORD nType;   // Packet Opcode / Category identifier
};
```

### Encryption Pipeline
1. **Header Inspection**: Packet length (`dwSize`) is validated against minimum and maximum bounds (`NET_DATA_BUFSIZE = 8192`).
2. **Payload Decryption**: Sensitive credentials and trade packets are decrypted using TEA/XTEA block ciphers (`minTea.cpp:80`).
3. **Payload Decompression**: Large state sync packets (e.g. initial zone entity load, character lists) are compressed with LZO (`MinLzo.cpp:45`).
4. **Opcode Dispatch**: Packets are dispatched to subsystem handlers via opcode switch tables (`GLGaeaServer::MsgProcess`, `GLAgentServer::MsgProcess`).

---

## Network Security Best Practices

- **Zero Shell Spawning**: Never invoke OS commands (`WinExec`, `netsh`) in socket worker threads (`s_CClientManager.cpp:1318`).
- **Connection Rate Limiting**: Limit simultaneous TCP connections per IP using `m_wClientIPMax` and in-memory hash sets in `CIPFilter`.
- **Buffer Overflow Protection**: Validate incoming `nmg->dwSize == sizeof(ExpectedStruct)` before casting raw packet buffers to structured pointers.
