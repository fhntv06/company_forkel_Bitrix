<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); } ?>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <!-- Плоская длинная тень накладки 150×150 (косой свет слева сверху) -->
        <polygon id="shade150" class="pl-shade" points="150,0 270,120 270,270 120,270 0,150 150,150"/>
    </defs>
    <symbol id="p-switch" viewBox="0 0 150 150">
        <rect class="pl-face" x="0.5" y="0.5" width="149" height="149" rx="3"/>
        <rect class="pl-inner" x="14" y="14" width="122" height="122" rx="2"/>
        <rect class="pl-key" x="28" y="28" width="94" height="94" rx="2"/>
        <path class="pl-line" d="M28 52H122"/>
    </symbol>
    <symbol id="p-double" viewBox="0 0 150 150">
        <rect class="pl-face" x="0.5" y="0.5" width="149" height="149" rx="3"/>
        <rect class="pl-inner" x="14" y="14" width="122" height="122" rx="2"/>
        <rect class="pl-key" x="24" y="28" width="47" height="94" rx="2"/>
        <rect class="pl-key" x="79" y="28" width="47" height="94" rx="2"/>
        <path class="pl-line" d="M24 52H71M79 52H126"/>
    </symbol>
    <symbol id="p-socket" viewBox="0 0 150 150">
        <rect class="pl-face" x="0.5" y="0.5" width="149" height="149" rx="3"/>
        <circle class="pl-inner" cx="75" cy="75" r="50"/>
        <circle class="pl-key" cx="75" cy="75" r="38"/>
        <circle class="pl-hole" cx="62" cy="75" r="4.5"/>
        <circle class="pl-hole" cx="88" cy="75" r="4.5"/>
        <rect class="pl-hole" x="71" y="40" width="8" height="7" rx="1"/>
        <rect class="pl-hole" x="71" y="103" width="8" height="7" rx="1"/>
    </symbol>
    <symbol id="p-frame" viewBox="0 0 150 150">
        <rect class="pl-face" x="0.5" y="0.5" width="149" height="149" rx="3"/>
        <rect class="pl-open" x="22" y="22" width="106" height="106" rx="1"/>
    </symbol>
    <symbol id="room" viewBox="0 0 400 300">
        <path class="pl-room" d="M0 250H400M0 236H400"/>
        <rect class="pl-room" x="36" y="58" width="92" height="178"/>
        <path class="pl-room" d="M36 58h92M112 150v8"/>
        <rect class="pl-room" x="236" y="64" width="120" height="104"/>
        <path class="pl-room" d="M296 64v104M236 116h120"/>
    </symbol>
    <clipPath id="wall-clip">
        <rect x="0" y="0" width="840" height="540"/>
    </clipPath>
</svg>

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="sheen" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity=".35"/>
            <stop offset=".5" stop-color="#fff" stop-opacity="0"/>
        </linearGradient>
        <filter id="lift" x="-20%" y="-20%" width="140%" height="160%">
            <feDropShadow dx="0" dy="14" stdDeviation="14" flood-color="#2B2B2B" flood-opacity=".18"/>
        </filter>
    </defs>
    <!-- Рамка на три поста: цвета рамки и механизма задаются переменными --frame и --mech (условная графика, не фото) -->
    <symbol id="set" viewBox="0 0 520 200">
        <rect width="520" height="200" rx="16" fill="var(--frame)"/>
        <rect x=".5" y=".5" width="519" height="199" rx="15.5" fill="url(#sheen)" stroke="#2B2B2B"
              stroke-opacity=".14"/>
        <g fill="var(--mech)" stroke="#2B2B2B" stroke-opacity=".16">
            <rect x="22" y="25" width="150" height="150" rx="8"/>
            <rect x="185" y="25" width="150" height="150" rx="8"/>
            <rect x="348" y="25" width="150" height="150" rx="8"/>
        </g>
        <rect x="34" y="37" width="126" height="126" rx="6" fill="url(#sheen)" stroke="#2B2B2B" stroke-opacity=".12"
              fill-opacity=".9"/>
        <circle cx="260" cy="100" r="50" fill="#2B2B2B" fill-opacity=".1"/>
        <circle cx="260" cy="100" r="46" fill="none" stroke="#fff" stroke-opacity=".4" stroke-width="2"/>
        <circle cx="241" cy="100" r="6" fill="#2B2B2B"/>
        <circle cx="279" cy="100" r="6" fill="#2B2B2B"/>
        <circle cx="423" cy="100" r="44" fill="url(#sheen)" stroke="#2B2B2B" stroke-opacity=".2"/>
        <circle cx="423" cy="100" r="30" fill="none" stroke="#2B2B2B" stroke-opacity=".18"/>
        <rect x="420" y="62" width="6" height="16" rx="3" fill="#2B2B2B" fill-opacity=".5"/>
    </symbol>
    <symbol id="arrow" viewBox="0 0 24 24">
        <path d="M7 17L17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
              stroke-linejoin="round"/>
    </symbol>
</svg>
