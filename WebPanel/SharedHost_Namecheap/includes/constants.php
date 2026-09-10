<?php
/**
 * RanOnline Constants & Definitions
 * Mapped directly from engine sources:
 * - Lib_Client/G-Logic/GLCharDefine.h
 * - Lib_Client/G-Logic/GLGaeaServer.h
 */

// ── Character Classes ──────────────────────────────────────────
const RAN_CLASSES = [
    0  => ['name' => 'Brawler (M)',    'short' => 'BR-M', 'icon' => 'brawler_m'],
    1  => ['name' => 'Swordsman (M)',  'short' => 'SW-M', 'icon' => 'swordsman_m'],
    2  => ['name' => 'Archer (F)',     'short' => 'AR-F', 'icon' => 'archer_f'],
    3  => ['name' => 'Shaman (F)',     'short' => 'SH-F', 'icon' => 'shaman_f'],
    4  => ['name' => 'Extreme (M)',    'short' => 'EX-M', 'icon' => 'extreme_m'],
    5  => ['name' => 'Extreme (F)',    'short' => 'EX-F', 'icon' => 'extreme_f'],
    6  => ['name' => 'Brawler (F)',    'short' => 'BR-F', 'icon' => 'brawler_f'],
    7  => ['name' => 'Swordsman (F)',  'short' => 'SW-F', 'icon' => 'swordsman_f'],
    8  => ['name' => 'Archer (M)',     'short' => 'AR-M', 'icon' => 'archer_m'],
    9  => ['name' => 'Shaman (M)',     'short' => 'SH-M', 'icon' => 'shaman_m'],
    10 => ['name' => 'Gunner (M)',     'short' => 'GN-M', 'icon' => 'gunner_m'],
    11 => ['name' => 'Gunner (F)',     'short' => 'GN-F', 'icon' => 'gunner_f'],
    12 => ['name' => 'Assassin (M)',   'short' => 'AS-M', 'icon' => 'assassin_m'],
    13 => ['name' => 'Assassin (F)',   'short' => 'AS-F', 'icon' => 'assassin_f'],
    14 => ['name' => 'Tricker (M)',    'short' => 'TR-M', 'icon' => 'tricker_m'],
    15 => ['name' => 'Tricker (F)',    'short' => 'TR-F', 'icon' => 'tricker_f'],
];

// ── Schools / Campuses ─────────────────────────────────────────
const RAN_SCHOOLS = [
    0 => ['name' => 'Sacred Gate', 'abbr' => 'SG', 'color' => '#3498db'],
    1 => ['name' => 'Mystic Peak', 'abbr' => 'MP', 'color' => '#e74c3c'],
    2 => ['name' => 'Phoenix',     'abbr' => 'PH', 'color' => '#2ecc71'],
];

// ── User Levels (Authority) ────────────────────────────────────
const USER_COMMON = 1;
const USER_GM1    = 2;
const USER_GM2    = 4;
const USER_GM3    = 8;
const USER_GM4    = 19; // Master Admin

function getClassName(int $classId): string {
    return RAN_CLASSES[$classId]['name'] ?? 'Unknown Class';
}

function getSchoolName(int $schoolId): string {
    return RAN_SCHOOLS[$schoolId]['name'] ?? 'Unknown Campus';
}

function getSchoolColor(int $schoolId): string {
    return RAN_SCHOOLS[$schoolId]['color'] ?? '#95a5a6';
}
