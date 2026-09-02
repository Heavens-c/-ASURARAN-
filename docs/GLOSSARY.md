# RanOnline MMO Glossary & Opcode Taxonomy

## Project-Specific Terminology

- **Agent Server (`ServerAgent`)**: Gateway server managing character lobby, cross-field player handoffs, global/whisper chats, and party/club structures.
- **Field Server (`ServerField`)**: Map/zone execution daemon running `GLGaeaServer`, spatial physics, combat simulation, and NPC/Monster AI.
- **Session Server (`ServerSession`)**: Central inter-server bus coordinating server statuses, load balancing, and single-login enforcement across the cluster.
- **Gaea Server (`GLGaeaServer`)**: The primary server-side game engine orchestrator inside `ServerField`.
- **GLLandMan**: Spatial map manager instance responsible for collision, sector cells, and active entity lists.
- **GLChar / GLCharAG**: Character game entity representations on Field and Agent servers.
- **GLCrow**: Monster and non-player character (NPC) entity state machine.
- **G-Logic**: Core gameplay rule library (`Lib_Client/G-Logic/`) containing game formulas, items, skills, quests, and arenas.
- **RCC (`CryptionRCC`)**: Ran Compressed Container archive format used for packing client data assets, textures, and scripts.

---

## User Level & Permission Taxonomy (`EMUSERTYPE`)

| Constant | Value | Description |
| :--- | :--- | :--- |
| `USER_COMMON` | `0` | Standard player account (no elevated privileges). |
| `USER_GM1` | `1` | Junior Game Master (chat monitoring, announcement capabilities). |
| `USER_GM2` | `2` | Mid-level Game Master (kick, mute, teleport capabilities). |
| `USER_GM3` | `3` | Senior Game Master (event controls, mob spawning, item inspection). |
| `USER_MASTER` | `4` | Server Administrator / Master (full console and database control). |

---

## Key Packet Opcode Categories (`s_NetGlobal.h`)

| Category Prefix | Base Value | Description |
| :--- | :--- | :--- |
| `NET_MSG_LOGIN_` | `1000+` | Account authentication, version checks, and server selection. |
| `NET_MSG_AGENT_` | `2000+` | Agent lobby management, character selection, creation, and deletion. |
| `NET_MSG_GCTRL_` | `3000+` | Real-time game control: movement (`GOTO`), skills, inventory, and stats. |
| `NET_MSG_CHAT_` | `4000+` | Chat channel broadcasting (general, party, guild, whisper, shout). |
| `NET_MSG_TRADE_` | `5000+` | 2-player secure trade escrow negotiation and exchange. |
| `NET_MSG_GM_` | `6000+` | Administrative and Game Master operations. |
| `NET_MSG_PVP_` | `7000+` | Structured arena instances (Tyranny, School Wars, Capture The Flag). |
