# Pokemon Battle Arena

A turn-based `Pokemon battle arena` built with PHP OOP principles, featuring dual-type Pokemon, type effectiveness, and strategic combat.

## Features

- **2-Player Battle System**: Real-time turn-based combat
- **Dual-Type Pokemon**: Authentic Pokemon with multiple types
- **Type Effectiveness**: Strategic damage calculations with STAB bonus
- **Team Selection**: Each player selects 3 Pokemon from 16 available
- **Battle Actions**: Attack, switch Pokemon, and use items
- **Background Music**: Immersive battle soundtrack
- **Responsive Design**: Works on desktop and mobile devices

## Technology Stack

- **Backend**: PHP 8.0+
- **Frontend**: HTML5, CSS3, JavaScript
- **OOP Principles**: All 4 pillars implemented
- **Single File**: Complete game in one index.php file

## Quick Start

1. Place the `index.php` file in your web server directory
2. Ensure PHP 8.0+ is installed
3. Ensure that you have **PHP Intelephense** + **PHP Server** in your `VSCODE`
4. Create a `music` folder and add `POKEMONBATTLESOUND.mp3`
5. Open the file with PHP Server

## Contributions
Villanueva - PHP, JS, CSS
Ruiz - PHP
Barona - JS
Movilla - HTML CSS
Brian - PHP

## Game Rules

- Each player selects 3 Pokemon for their team
- Fastest Pokemon is always first
- Players take turns choosing actions (attack, switch, item)
- Battle continues until all Pokemon on one team faint
- Type effectiveness and STAB bonuses affect damage
- First player to defeat all opponent's Pokemon wins

## 4 Pillars  

- Encapsulation 
To implement it, properties in classes like Pokémon, Attack, and PokémonBattle were declared private or protected, and only public methods like getters, damage, heal, and status-related functions could access or modify them.  Important information like health, speed, attacks, and battle state cannot be accessed directly as a result. 

- Abstraction 
The BattleAction interface and the abstract BattleEntity class, which define common methods without full implementation, are examples of abstraction in action. These structures conceal unnecessary internal details while forcing child classes to keep to a mandatory format. 

- Inheritance 
When the Pokémon class extends the BattleEntity class, inheritance is used, enabling Pokémon to reuse shared behaviors and attributes like name, health, damage handling, and faint checking without having to rewrite them. 

- Polymorphism 
The battle system makes use of polymorphism so that various action classes, such as attack, switch, and item, all use the same BattleAction interface but carry out distinct actions when they are executed. This enables the application to manage several behaviors with a single action flow. 
