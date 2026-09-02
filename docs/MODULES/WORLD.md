# Subsystem: World, Zoning & Gameplay Execution

## Purpose
The World subsystem manages spatial map execution, character movement validation, monster and NPC AI logic, combat damage calculations, skill executions, and competitive PvP instance arenas.

---

## Key Files & Classes

| File | Component / Class | Responsibility |
| :--- | :--- | :--- |
| `Lib_Client/G-Logic/GLGaeaServer.h/.cpp` | `GLGaeaServer` | Main Field Server world engine, land manager container, entity registry. |
| `Lib_Client/G-Logic/GLLandMan.h/.cpp` | `GLLandMan` | Map instance, spatial cell grid, gate portals, mob generator triggers. |
| `Lib_Client/G-Logic/GLChar.h/.cpp` | `GLChar` | Server-authoritative character entity (stats, buffs, inventory, cooldowns). |
| `Lib_Client/G-Logic/GLCrow.h/.cpp` | `GLCrow` | Monster/NPC entity state machine, aggro table, behavior tree, drop schedule. |
| `Lib_Client/G-Logic/GLSkill.h/.cpp` | `GLSkill` | Skill definitions, damage formulas, area-of-effect calculations, buffs. |
| `Lib_Client/G-Logic/GLCharSkillMsg.cpp` | `GLChar::MsgReqSkill` | Skill execution packet handler, cooldown/cost validation. |
| `Lib_Client/G-Logic/GLPVPTyranny.cpp` | `GLPVPTyranny` | Tyranny School War instance arena manager. |
| `Lib_Client/G-Logic/GLPVPCaptureTheFlag.cpp` | `GLPVPCaptureTheFlag` | Capture The Flag (CTF) PvP instance logic. |

---

## Spatial Zoning & Movement Validation

- **Spatial Partitioning**: Maps are loaded into `GLLandMan` with NavMesh navigation meshes and quad-tree spatial sectors.
- **Position Tracking**:
  - Client sends `NET_MSG_GCTRL_GOTO` (`GLCharMsg.cpp:252`).
  - Server computes distance delta `vDist = m_vPos - pNetMsg->vCurPos`.
  - **Speed Hack & Teleport Guard**: If `D3DXVec3Length(&vDist) > 60.0f`, the server rejects the position and forces the client back to authoritative coordinates (`GLCharMsg.cpp:270`).
- **Zone Handoffs**:
  - Map transfers query `DxLandGateMan` (`dxincommand.cpp:250`).
  - Agent coordinates transfer between Field Server instances using `NET_MSG_CHARPOS_FROMDB2AGT` (`GLAgentServerMsg.cpp:5975`).

---

## Combat & Damage Pipeline

1. **Skill Initiation**: Client sends `NET_MSG_GCTRL_REQ_SKILL` with `sSkillID` and `dwTargetID`.
2. **Server-Side Validation**:
   - `GLChar::MsgReqSkill()` (`GLCharSkillMsg.cpp:222`) verifies:
     - Skill is learned in `m_ExpSkills`.
     - Skill cooldown is elapsed (`m_sSUM_SKILL.fDelay`).
     - Character has sufficient MP/SP/CP.
     - Target is in line-of-sight and within `fRange`.
3. **Damage Calculation**:
   - Damage is computed using server-side stats: Base Attack + Weapon Power + Skill Modifier - Target Defense/Elemental Resistance.
   - Client-reported damage values are never accepted.
4. **Broadcast & HP Sync**:
   - Damage event broadcasts to nearby players via `SendMsgViewAround(NET_MSG_GCTRL_SKILL_BRD)`.
   - Authoritative HP update sent to target entity.
