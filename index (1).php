<?php
// Start output buffering to prevent headers already sent errors
ob_start();
session_start();

// OOP Implementation with all four pillars

// 1. ABSTRACTION: Abstract base class for battle entities
abstract class BattleEntity {
    protected $name;
    protected $health;
    protected $maxHealth;
    
    public function __construct($name, $health) {
        $this->name = $name;
        $this->health = $health;
        $this->maxHealth = $health;
    }
    
    abstract public function getType();
    abstract public function getAttacks();
    
    public function getName() {
        return $this->name;
    }
    
    public function getHealth() {
        return $this->health;
    }
    
    public function getMaxHealth() {
        return $this->maxHealth;
    }
    
    public function isFainted() {
        return $this->health <= 0;
    }
    
    public function takeDamage($damage) {
        $this->health -= $damage;
        if ($this->health < 0) $this->health = 0;
        return $damage;
    }
    
    public function heal($amount) {
        $this->health += $amount;
        if ($this->health > $this->maxHealth) $this->health = $this->maxHealth;
        return $amount;
    }
}

// 2. INHERITANCE: Pokemon class extends BattleEntity
class Pokemon extends BattleEntity {
    private $types;
    private $attacks;
    private $speed;
    private $image;
    
    public function __construct($name, $types, $health, $speed, $attacks, $image) {
        parent::__construct($name, $health);
        $this->types = is_array($types) ? $types : [$types];
        $this->speed = $speed;
        $this->attacks = $attacks;
        $this->image = $image;
    }
    
    // 3. ENCAPSULATION: Private properties with public getters
    public function getType() {
        return count($this->types) > 1 ? implode('/', $this->types) : $this->types[0];
    }
    
    public function getTypes() {
        return $this->types;
    }
    
    public function getAttacks() {
        return $this->attacks;
    }
    
    public function getSpeed() {
        return $this->speed;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function getStatus() {
        $typeStr = count($this->types) > 1 ? implode('/', $this->types) : $this->types[0];
        return "{$this->name} ({$typeStr}) - HP: {$this->health}/{$this->maxHealth}";
    }
}

// Attack class
class Attack {
    private $name;
    private $power;
    private $type;
    private $accuracy;
    
    public function __construct($name, $power, $type, $accuracy = 90) {
        $this->name = $name;
        $this->power = $power;
        $this->type = $type;
        $this->accuracy = $accuracy;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getPower() {
        return $this->power;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function getAccuracy() {
        return $this->accuracy;
    }
    
    public function calculateDamage($attackerTypes, $defenderTypes) {
        $damage = $this->power;
        
        // Type effectiveness - check against all defender types
        $effectiveness = 1.0;
        foreach ($defenderTypes as $defenderType) {
            $effectiveness *= $this->getEffectiveness($this->type, $defenderType);
        }
        $damage *= $effectiveness;
        
        // STAB (Same Type Attack Bonus) - 1.5x if attacker has the same type
        $stab = 1.0;
        foreach ($attackerTypes as $attackerType) {
            if ($attackerType === $this->type) {
                $stab = 1.5;
                break;
            }
        }
        $damage *= $stab;
        
        // Random variation (85% to 100%)
        $damage *= (0.85 + (mt_rand(0, 15) / 100));
        
        return [
            'damage' => (int)round($damage),
            'effectiveness' => $effectiveness,
            'stab' => $stab
        ];
    }
    
    private function getEffectiveness($attackType, $defenderType) {
        $effectivenessChart = [
            'Normal' => ['Rock' => 0.5, 'Steel' => 0.5, 'Ghost' => 0],
            'Fire' => ['Fire' => 0.5, 'Water' => 0.5, 'Grass' => 2.0, 'Ice' => 2.0, 'Bug' => 2.0, 'Rock' => 0.5, 'Dragon' => 0.5, 'Steel' => 2.0],
            'Water' => ['Fire' => 2.0, 'Water' => 0.5, 'Grass' => 0.5, 'Ground' => 2.0, 'Rock' => 2.0, 'Dragon' => 0.5],
            'Grass' => ['Fire' => 0.5, 'Water' => 2.0, 'Grass' => 0.5, 'Poison' => 0.5, 'Ground' => 2.0, 'Flying' => 0.5, 'Bug' => 0.5, 'Rock' => 2.0, 'Dragon' => 0.5, 'Steel' => 0.5],
            'Electric' => ['Water' => 2.0, 'Electric' => 0.5, 'Grass' => 0.5, 'Ground' => 0, 'Flying' => 2.0, 'Dragon' => 0.5],
            'Ice' => ['Fire' => 0.5, 'Water' => 0.5, 'Grass' => 2.0, 'Ice' => 0.5, 'Ground' => 2.0, 'Flying' => 2.0, 'Dragon' => 2.0, 'Steel' => 0.5],
            'Fighting' => ['Normal' => 2.0, 'Ice' => 2.0, 'Poison' => 0.5, 'Flying' => 0.5, 'Psychic' => 0.5, 'Bug' => 0.5, 'Rock' => 2.0, 'Ghost' => 0, 'Dark' => 2.0, 'Steel' => 2.0, 'Fairy' => 0.5],
            'Poison' => ['Grass' => 2.0, 'Poison' => 0.5, 'Ground' => 0.5, 'Rock' => 0.5, 'Ghost' => 0.5, 'Steel' => 0, 'Fairy' => 2.0],
            'Ground' => ['Fire' => 2.0, 'Electric' => 2.0, 'Grass' => 0.5, 'Poison' => 2.0, 'Flying' => 0, 'Bug' => 0.5, 'Rock' => 2.0, 'Steel' => 2.0],
            'Flying' => ['Electric' => 0.5, 'Grass' => 2.0, 'Fighting' => 2.0, 'Bug' => 2.0, 'Rock' => 0.5, 'Steel' => 0.5],
            'Psychic' => ['Fighting' => 2.0, 'Poison' => 2.0, 'Psychic' => 0.5, 'Dark' => 0, 'Steel' => 0.5],
            'Bug' => ['Fire' => 0.5, 'Grass' => 2.0, 'Fighting' => 0.5, 'Poison' => 0.5, 'Flying' => 0.5, 'Psychic' => 2.0, 'Ghost' => 0.5, 'Dark' => 2.0, 'Steel' => 0.5, 'Fairy' => 0.5],
            'Rock' => ['Fire' => 2.0, 'Ice' => 2.0, 'Fighting' => 0.5, 'Ground' => 0.5, 'Flying' => 2.0, 'Bug' => 2.0, 'Steel' => 0.5],
            'Ghost' => ['Normal' => 0, 'Psychic' => 2.0, 'Ghost' => 2.0, 'Dark' => 0.5],
            'Dragon' => ['Dragon' => 2.0, 'Steel' => 0.5, 'Fairy' => 0],
            'Dark' => ['Fighting' => 0.5, 'Psychic' => 2.0, 'Ghost' => 2.0, 'Dark' => 0.5, 'Fairy' => 0.5],
            'Steel' => ['Fire' => 0.5, 'Water' => 0.5, 'Electric' => 0.5, 'Ice' => 2.0, 'Rock' => 2.0, 'Steel' => 0.5, 'Fairy' => 2.0],
            'Fairy' => ['Fire' => 0.5, 'Fighting' => 2.0, 'Poison' => 0.5, 'Dragon' => 2.0, 'Dark' => 2.0, 'Steel' => 0.5]
        ];
        
        return $effectivenessChart[$attackType][$defenderType] ?? 1.0;
    }
}

// 4. POLYMORPHISM: Different battle actions with same interface
interface BattleAction {
    public function execute($battle, $player);
    public function getDescription();
}

class AttackAction implements BattleAction {
    private $attackIndex;
    
    public function __construct($attackIndex) {
        $this->attackIndex = $attackIndex;
    }
    
    public function execute($battle, $player) {
        return $battle->executeAttack($player, $this->attackIndex);
    }
    
    public function getDescription() {
        return "used an attack";
    }
}

class SwitchAction implements BattleAction {
    private $pokemonIndex;
    
    public function __construct($pokemonIndex) {
        $this->pokemonIndex = $pokemonIndex;
    }
    
    public function execute($battle, $player) {
        return $battle->switchPokemon($player, $this->pokemonIndex);
    }
    
    public function getDescription() {
        return "switched Pokemon";
    }
}

class ItemAction implements BattleAction {
    public function execute($battle, $player) {
        return $battle->useItem($player);
    }
    
    public function getDescription() {
        return "used an item";
    }
}

// Battle System
class PokemonBattle {
    private $player1;
    private $player2;
    private $player1Pokemon;
    private $player2Pokemon;
    private $currentTurn;
    private $battleLog;
    private $gameOver;
    private $winner;
    private $showAnimation;
    private $targetPlayer;
    
    public function __construct($player1Name, $player2Name, $player1Team, $player2Team) {
        $this->player1 = $player1Name;
        $this->player2 = $player2Name;
        $this->battleLog = [];
        $this->gameOver = false;
        $this->winner = null;
        $this->showAnimation = false;
        $this->targetPlayer = null;
        
        // Initialize Pokemon for both players from their selected teams
        $this->player1Pokemon = $player1Team;
        $this->player2Pokemon = $player2Team;
        
        // THEN determine who goes first
        $this->currentTurn = $this->getFirstTurnPlayer();
        
        $this->addToLog("Battle started between {$this->player1} and {$this->player2}!");
        $this->addToLog("{$this->currentTurn} goes first!");
    }
    
    private function getFirstTurnPlayer() {
        // Player with faster active Pokemon goes first
        $speed1 = $this->player1Pokemon[0]->getSpeed();
        $speed2 = $this->player2Pokemon[0]->getSpeed();
        
        if ($speed1 == $speed2) {
            return mt_rand(0, 1) == 0 ? $this->player1 : $this->player2;
        }
        
        return $speed1 > $speed2 ? $this->player1 : $this->player2;
    }
    
    public function executeTurn($player, $action) {
        if ($this->gameOver || $player !== $this->currentTurn) {
            return ["success" => false, "message" => "It's not your turn!"];
        }
        
        $result = $action->execute($this, $player);
        
        if ($result["success"]) {
            $this->addToLog("{$player} " . $action->getDescription());
            
            // Check if battle is over
            $this->checkBattleOver();
            
            // Switch turn if battle continues
            if (!$this->gameOver) {
                $this->currentTurn = ($this->currentTurn === $this->player1) ? $this->player2 : $this->player1;
                $this->addToLog("{$this->currentTurn}'s turn!");
            }
        }
        
        return $result;
    }
    
    // ATTACK OR EXECUTOR

    public function executeAttack($player, $attackIndex) {
        if ($player === $this->player1) {
            $attacker = $this->player1Pokemon[0];
            $defender = $this->player2Pokemon[0];
            $this->targetPlayer = $this->player2;
        } else {
            $attacker = $this->player2Pokemon[0];
            $defender = $this->player1Pokemon[0];
            $this->targetPlayer = $this->player1;
        }
        
        // Check if attacker or defender is fainted
        if ($attacker->isFainted()) {
            return ["success" => false, "message" => "Your Pokemon has fainted! Switch Pokemon."];
        }
        
        if ($defender->isFainted()) {
            return ["success" => false, "message" => "The opponent's Pokemon has fainted!"];
        }
        
        $attacks = $attacker->getAttacks();
        if ($attackIndex < 0 || $attackIndex >= count($attacks)) {
            return ["success" => false, "message" => "Invalid attack selection!"];
        }
        
        $attack = $attacks[$attackIndex];
        
        // Check accuracy
        if (mt_rand(1, 100) > $attack->getAccuracy()) {
            $this->addToLog("{$attacker->getName()}'s attack missed!");
            return ["success" => true, "message" => "The attack missed!"];
        }
        
        $damageResult = $attack->calculateDamage($attacker->getTypes(), $defender->getTypes());
        $damage = $damageResult['damage'];
        $effectiveness = $damageResult['effectiveness'];
        $stab = $damageResult['stab'];
        
        $defender->takeDamage($damage);
        
        $effectivenessMsg = $this->getEffectivenessMessage($effectiveness);
        $stabMsg = $stab > 1 ? " STAB bonus!" : "";
        
        $this->addToLog("{$attacker->getName()} used {$attack->getName()}!{$stabMsg} {$effectivenessMsg}");
        $this->addToLog("It dealt {$damage} damage to {$defender->getName()}!");
        
        if ($defender->isFainted()) {
            $this->addToLog("{$defender->getName()} fainted!");
        }
        
        // Set animation flag and target
        $this->showAnimation = true;
        
        return [
            "success" => true, 
            "message" => "Attack successful!",
            "damage" => $damage,
            "effectiveness" => $effectivenessMsg,
            "stab" => $stabMsg,
            "show_animation" => true,
            "target_player" => $this->targetPlayer
        ];
    }

    // SWAPPER
    public function switchPokemon($player, $pokemonIndex) {
        if ($player === $this->player1) {
            $pokemon = &$this->player1Pokemon;
        } else {
            $pokemon = &$this->player2Pokemon;
        }
        
        if ($pokemonIndex < 0 || $pokemonIndex >= count($pokemon)) {
            return ["success" => false, "message" => "Invalid Pokemon selection!"];
        }
        
        if ($pokemonIndex === 0) {
            return ["success" => false, "message" => "This Pokemon is already in battle!"];
        }
        
        if ($pokemon[$pokemonIndex]->isFainted()) {
            return ["success" => false, "message" => "This Pokemon has fainted and cannot battle!"];
        }
        
        // Swap positions
        $temp = $pokemon[0];
        $pokemon[0] = $pokemon[$pokemonIndex];
        $pokemon[$pokemonIndex] = $temp;
        
        $this->addToLog("{$player} sent out {$pokemon[0]->getName()}!");
        
        return ["success" => true, "message" => "Pokemon switched!"];
    }
    
    // POTION OR HEAL OR ITEM SECTION
    public function useItem($player) {
        if ($player === $this->player1) {
            $pokemon = $this->player1Pokemon[0];
        } else {
            $pokemon = $this->player2Pokemon[0];
        }
        
        if ($pokemon->isFainted()) {
            return ["success" => false, "message" => "This Pokemon has fainted and cannot use items!"];
        }
        
        $healAmount = 70;
        $actualHeal = $pokemon->heal($healAmount);
        
        $this->addToLog("{$player} used a Potion on {$pokemon->getName()}!");
        $this->addToLog("It restored {$actualHeal} HP!");
        
        return ["success" => true, "message" => "Item used successfully!"];
    }
    
    private function getEffectivenessMessage($effectiveness) {
        if ($effectiveness > 1) {
            return "It's super effective!";
        } elseif ($effectiveness < 1 && $effectiveness > 0) {
            return "It's not very effective...";
        } elseif ($effectiveness == 0) {
            return "It has no effect!";
        } else {
            return "";
        }
    }
    
    private function checkBattleOver() {
        $p1Fainted = true;
        foreach ($this->player1Pokemon as $pokemon) {
            if (!$pokemon->isFainted()) {
                $p1Fainted = false;
                break;
            }
        }
        
        $p2Fainted = true;
        foreach ($this->player2Pokemon as $pokemon) {
            if (!$pokemon->isFainted()) {
                $p2Fainted = false;
                break;
            }
        }
        
        if ($p1Fainted || $p2Fainted) {
            $this->gameOver = true;
            if ($p1Fainted && $p2Fainted) {
                $this->winner = "It's a tie!";
            } elseif ($p1Fainted) {
                $this->winner = $this->player2;
            } else {
                $this->winner = $this->player1;
            }
            
            $this->addToLog("Battle over! {$this->winner} wins!");
        }
    }
    
    // BattleLogger (TIP: to have longer log you can change the number)
    private function addToLog($message) {
        $this->battleLog[] = $message;
        if (count($this->battleLog) > 20) {
            array_shift($this->battleLog);
        }
    }
    
    // Getters
    public function getPlayer1() { return $this->player1; }
    public function getPlayer2() { return $this->player2; }
    public function getPlayer1Pokemon() { return $this->player1Pokemon; }
    public function getPlayer2Pokemon() { return $this->player2Pokemon; }
    public function getCurrentTurn() { return $this->currentTurn; }
    public function getBattleLog() { return $this->battleLog; }
    public function isGameOver() { return $this->gameOver; }
    public function getWinner() { return $this->winner; }
    public function shouldShowAnimation() { return $this->showAnimation; }
    public function getTargetPlayer() { return $this->targetPlayer; }
}

// Pokemon Attack Pool
function createPokemonPool() {
    // BASIC ATTACKS
    $tackle = new Attack("Tackle", 40, "Normal", 100);
    $quickAttack = new Attack("Quick Attack", 40, "Normal", 100);
    $bodySlam = new Attack("Body Slam", 85, "Normal", 100);
    $hyperBeam = new Attack("Hyper Beam", 150, "Normal", 90);
    $extremespeed = new Attack("Extreme Speed", 80, "Normal", 100);
    $swift = new Attack("Swift", 60, "Normal", 100);
    $slash = new Attack("Slash", 70, "Normal", 100);
    $round = new Attack("Round", 60, "Normal", 100);
    
    // FIRE ATTACKS
    $ember = new Attack("Ember", 40, "Fire", 100);
    $flamethrower = new Attack("Flamethrower", 90, "Fire", 85);
    $fireBlast = new Attack("Fire Blast", 110, "Fire", 85);
    $sacredFire = new Attack("Sacred Fire", 100, "Fire", 95);
    $fusionFlare = new Attack("Fusion Flare", 100, "Fire", 100);
    $blueFlare = new Attack("Blue Flare", 130, "Fire", 85);
    $heatWave = new Attack("Heat Wave", 95, "Fire", 90);
    $lavaPlume = new Attack("Lava Plume", 80, "Fire", 100);
    $fireFang = new Attack("Fire Fang", 65, "Fire", 95);
    $flareBlitz = new Attack("Flare Blitz", 120, "Fire", 100);
    $blastBurn = new Attack("Blast Burn", 150, "Fire", 90);
    $overheat = new Attack("Overheat", 130, "Fire", 90);
    $firePunch = new Attack("Fire Punch", 75, "Fire", 100);
    
    // WATER ATTACKS
    $waterGun = new Attack("Water Gun", 40, "Water", 100);
    $hydroPump = new Attack("Hydro Pump", 110, "Water", 80);
    $surf = new Attack("Surf", 90, "Water", 100);
    $aquaTail = new Attack("Aqua Tail", 90, "Water", 90);
    $originPulse = new Attack("Origin Pulse", 110, "Water", 85);
    $waterPulse = new Attack("Water Pulse", 60, "Water", 100);
    $muddyWater = new Attack("Muddy Water", 90, "Water", 85);
    $waterfall = new Attack("Waterfall", 80, "Water", 100);
    $scald = new Attack("Scald", 80, "Water", 100);
    $whirlpool = new Attack("Whirlpool", 35, "Water", 85);
    $hydroCannon = new Attack("Hydro Cannon", 150, "Water", 90);
    $bubbleBeam = new Attack("Bubble Beam", 65, "Water", 100);
    $brine = new Attack("Brine", 65, "Water", 100);
    $waterSpout = new Attack("Water Spout", 150, "Water", 100);
    
    // GRASS ATTACKS
    $vineWhip = new Attack("Vine Whip", 40, "Grass", 100);
    $solarBeam = new Attack("Solar Beam", 120, "Grass", 100);
    $leafBlade = new Attack("Leaf Blade", 90, "Grass", 100);
    $energyBall = new Attack("Energy Ball", 90, "Grass", 100);
    $leafStorm = new Attack("Leaf Storm", 130, "Grass", 90);
    $frenzyPlant = new Attack("Frenzy Plant", 150, "Grass", 90);
    $seedBomb = new Attack("Seed Bomb", 80, "Grass", 100);
    $woodHammer = new Attack("Wood Hammer", 120, "Grass", 100);
    $powerWhip = new Attack("Power Whip", 120, "Grass", 85);
    $gigaDrain = new Attack("Giga Drain", 75, "Grass", 100);
    $magicalLeaf = new Attack("Magical Leaf", 60, "Grass", 100);
    
    // ELECTRIC ATTACKS
    $thunderShock = new Attack("Thunder Shock", 40, "Electric", 100);
    $thunderbolt = new Attack("Thunderbolt", 90, "Electric", 100);
    $thunder = new Attack("Thunder", 110, "Electric", 70);
    $fusionBolt = new Attack("Fusion Bolt", 100, "Electric", 100);
    $voltSwitch = new Attack("Volt Switch", 70, "Electric", 100);
    $boltStrike = new Attack("Bolt Strike", 130, "Electric", 85);
    $volttackle = new Attack("Volt Tackle", 120, "Electric", 100);
    $thunderFang = new Attack("Thunder Fang", 65, "Electric", 95);
    $zapCannon = new Attack("Zap Cannon", 120, "Electric", 50);
    $discharge = new Attack("Discharge", 80, "Electric", 100);
    $electroBall = new Attack("Electro Ball", 60, "Electric", 100);
    $crossThunder = new Attack("Cross Thunder", 100, "Electric", 100);
    
    // ICE ATTACKS
    $iceShard = new Attack("Ice Shard", 40, "Ice", 100);
    $iceBeam = new Attack("Ice Beam", 90, "Ice", 100);
    $blizzard = new Attack("Blizzard", 110, "Ice", 70);
    $iceFang = new Attack("Ice Fang", 65, "Ice", 95);
    $freezeDry = new Attack("Freeze-Dry", 70, "Ice", 100);
    $icePunch = new Attack("Ice Punch", 75, "Ice", 100);
    $avalanche = new Attack("Avalanche", 60, "Ice", 100);
    $frostBreath = new Attack("Frost Breath", 60, "Ice", 90);
    $glaciate = new Attack("Glaciate", 65, "Ice", 95);
    
    // PSYCHIC ATTACKS
    $confusion = new Attack("Confusion", 50, "Psychic", 100);
    $psychic = new Attack("Psychic", 90, "Psychic", 100);
    $zenHeadbutt = new Attack("Zen Headbutt", 80, "Psychic", 90);
    $mistBall = new Attack("Mist Ball", 70, "Psychic", 100);
    $psyshock = new Attack("Psyshock", 80, "Psychic", 100);
    $futureSight = new Attack("Future Sight", 120, "Psychic", 100);
    $psychoCut = new Attack("Psycho Cut", 70, "Psychic", 100);
    $storedPower = new Attack("Stored Power", 20, "Psychic", 100);
    $psybeam = new Attack("Psybeam", 65, "Psychic", 100);
    
    // DARK ATTACKS
    $bite = new Attack("Bite", 60, "Dark", 100);
    $crunch = new Attack("Crunch", 80, "Dark", 100);
    $darkPulse = new Attack("Dark Pulse", 80, "Dark", 100);
    $nightSlash = new Attack("Night Slash", 70, "Dark", 100);
    $foulPlay = new Attack("Foul Play", 95, "Dark", 100);
    $suckerPunch = new Attack("Sucker Punch", 70, "Dark", 100);
    $knockOff = new Attack("Knock Off", 65, "Dark", 100);
    $throatChop = new Attack("Throat Chop", 80, "Dark", 100);
    
    // FAIRY ATTACKS
    $fairyWind = new Attack("Fairy Wind", 40, "Fairy", 100);
    $moonblast = new Attack("Moonblast", 95, "Fairy", 100);
    $dazzlingGleam = new Attack("Dazzling Gleam", 80, "Fairy", 100);
    $playRough = new Attack("Play Rough", 90, "Fairy", 90);
    $disarmingVoice = new Attack("Disarming Voice", 40, "Fairy", 100);
    $fairyBolt = new Attack("Fairy Bolt", 70, "Fairy", 100);
    
    // DRAGON ATTACKS
    $dragonBreath = new Attack("Dragon Breath", 60, "Dragon", 100);
    $dragonClaw = new Attack("Dragon Claw", 80, "Dragon", 100);
    $dragonPulse = new Attack("Dragon Pulse", 85, "Dragon", 100);
    $dracoMeteor = new Attack("Draco Meteor", 130, "Dragon", 90);
    $dragonRush = new Attack("Dragon Rush", 100, "Dragon", 75);
    $dragonTail = new Attack("Dragon Tail", 60, "Dragon", 90);
    $outrage = new Attack("Outrage", 120, "Dragon", 100);
    $dualChop = new Attack("Dual Chop", 40, "Dragon", 90);
    
    // FLYING ATTACKS
    $wingAttack = new Attack("Wing Attack", 60, "Flying", 100);
    $braveBird = new Attack("Brave Bird", 120, "Flying", 100);
    $aeroblast = new Attack("Aeroblast", 100, "Flying", 95);
    $drillPeck = new Attack("Drill Peck", 80, "Flying", 100);
    $airSlash = new Attack("Air Slash", 75, "Flying", 95);
    $hurricane = new Attack("Hurricane", 110, "Flying", 70);
    $oblivionWing = new Attack("Oblivion Wing", 80, "Flying", 100);
    $skyAttack = new Attack("Sky Attack", 140, "Flying", 90);
    $dragonAscent = new Attack("Dragon Ascent", 120, "Flying", 100);
    $peck = new Attack("Peck", 35, "Flying", 100);
    $aerialAce = new Attack("Aerial Ace", 60, "Flying", 100);
    $bounce = new Attack("Bounce", 85, "Flying", 85);
    
    // ROCK ATTACKS
    $rockThrow = new Attack("Rock Throw", 50, "Rock", 90);
    $stoneEdge = new Attack("Stone Edge", 100, "Rock", 80);
    $rockSlide = new Attack("Rock Slide", 75, "Rock", 90);
    $ancientPower = new Attack("Ancient Power", 60, "Rock", 100);
    $powerGem = new Attack("Power Gem", 80, "Rock", 100);
    $rockBlast = new Attack("Rock Blast", 25, "Rock", 90);
    $smackDown = new Attack("Smack Down", 50, "Rock", 100);
    
    // GROUND ATTACKS
    $earthquake = new Attack("Earthquake", 100, "Ground", 100);
    $precipiceBlades = new Attack("Precipice Blades", 120, "Ground", 85);
    $earthPower = new Attack("Earth Power", 90, "Ground", 100);
    $drillRun = new Attack("Drill Run", 80, "Ground", 95);
    $bulldoze = new Attack("Bulldoze", 60, "Ground", 100);
    $stompingTantrum = new Attack("Stomping Tantrum", 75, "Ground", 100);
    $highHorsepower = new Attack("High Horsepower", 95, "Ground", 95);
    
    // BUG ATTACKS
    $xScissor = new Attack("X-Scissor", 80, "Bug", 100);
    $bugBuzz = new Attack("Bug Buzz", 90, "Bug", 100);
    $signalBeam = new Attack("Signal Beam", 75, "Bug", 100);
    $megahorn = new Attack("Megahorn", 120, "Bug", 85);
    $uTurn = new Attack("U-turn", 70, "Bug", 100);
    $leechLife = new Attack("Leech Life", 80, "Bug", 100);
    $pinMissile = new Attack("Pin Missile", 25, "Bug", 95);
    
    // GHOST ATTACKS
    $shadowBall = new Attack("Shadow Ball", 80, "Ghost", 100);
    $shadowForce = new Attack("Shadow Force", 120, "Ghost", 100);
    $shadowClaw = new Attack("Shadow Claw", 70, "Ghost", 100);
    $shadowPunch = new Attack("Shadow Punch", 60, "Ghost", 100);
    $phantomForce = new Attack("Phantom Force", 90, "Ghost", 100);
    $hex = new Attack("Hex", 65, "Ghost", 100);
    $ominousWind = new Attack("Ominous Wind", 60, "Ghost", 100);
    
    // STEEL ATTACKS
    $ironHead = new Attack("Iron Head", 80, "Steel", 100);
    $flashCannon = new Attack("Flash Cannon", 80, "Steel", 100);
    $ironTail = new Attack("Iron Tail", 100, "Steel", 75);
    $meteorMash = new Attack("Meteor Mash", 90, "Steel", 90);
    $bulletPunch = new Attack("Bullet Punch", 40, "Steel", 100);
    $gyroBall = new Attack("Gyro Ball", 60, "Steel", 100);
    $steelWing = new Attack("Steel Wing", 70, "Steel", 90);
    $heavySlam = new Attack("Heavy Slam", 100, "Steel", 100);
    
    // FIGHTING ATTACKS
    $highJumpKick = new Attack("High Jump Kick", 130, "Fighting", 90);
    $auraSphere = new Attack("Aura Sphere", 80, "Fighting", 100);
    $focusBlast = new Attack("Focus Blast", 120, "Fighting", 70);
    $closeCombat = new Attack("Close Combat", 120, "Fighting", 100);
    $brickBreak = new Attack("Brick Break", 75, "Fighting", 100);
    $drainPunch = new Attack("Drain Punch", 75, "Fighting", 100);
    $crossChop = new Attack("Cross Chop", 100, "Fighting", 80);
    $dynamicPunch = new Attack("Dynamic Punch", 100, "Fighting", 50);
    $hammerArm = new Attack("Hammer Arm", 100, "Fighting", 90);
    $blazeKick = new Attack("Blaze Kick", 85, "Fire", 90);
    
    // POISON ATTACKS
    $sludgeBomb = new Attack("Sludge Bomb", 90, "Poison", 100);
    $poisonJab = new Attack("Poison Jab", 80, "Poison", 100);
    $sludgeWave = new Attack("Sludge Wave", 95, "Poison", 100);
    $gunkshot = new Attack("Gunk Shot", 120, "Poison", 80);
    $venoshock = new Attack("Venoshock", 65, "Poison", 100);
    $acidSpray = new Attack("Acid Spray", 40, "Poison", 100);
    $crossPoison = new Attack("Cross Poison", 70, "Poison", 100);
    
    // STATUS & OTHER (All converted to damaging moves)
    $judgment = new Attack("Judgment", 100, "Normal", 100);
    $crushGrip = new Attack("CrushGrip", 120, "Normal", 100);
    $rapidSpin = new Attack("Rapid Spin", 50, "Normal", 100);

    // ALL POKEMONS
    return [
        new Pokemon("Ho-oh", ["Fire", "Flying"], 320, 90, [$sacredFire, $braveBird, $earthquake, $flareBlitz], "https://img.pokemondb.net/sprites/black-white/anim/normal/ho-oh.gif"),
        new Pokemon("Moltres", ["Fire", "Flying"], 300, 90, [$flamethrower, $airSlash, $ancientPower, $heatWave], "https://img.pokemondb.net/sprites/black-white/anim/normal/moltres.gif"),
        new Pokemon("Lugia", ["Psychic", "Flying"], 330, 110, [$aeroblast, $psychic, $futureSight, $skyAttack], "https://img.pokemondb.net/sprites/black-white/anim/normal/lugia.gif"),
        new Pokemon("Rayquaza", ["Dragon", "Flying"], 310, 95, [$dragonAscent, $dragonClaw, $earthquake, $extremespeed], "https://img.pokemondb.net/sprites/black-white/anim/normal/rayquaza.gif"),
        new Pokemon("Groudon", ["Ground"], 320, 90, [$precipiceBlades, $fireBlast, $stoneEdge, $hammerArm], "https://img.pokemondb.net/sprites/black-white/anim/normal/groudon.gif"),
        new Pokemon("Regigigas", ["Normal"], 330, 100, [$crushGrip, $earthquake, $stoneEdge, $zenHeadbutt], "https://img.pokemondb.net/sprites/black-white/anim/normal/regigigas.gif"),
        new Pokemon("Giratina", ["Ghost", "Dragon"], 320, 90, [$shadowForce, $dragonPulse, $auraSphere, $ominousWind], "https://img.pokemondb.net/sprites/black-white/anim/normal/giratina.gif"),
        new Pokemon("Gyarados", ["Water", "Flying"], 300, 81, [$waterfall, $bounce, $earthquake, $iceFang], "https://img.pokemondb.net/sprites/black-white/anim/normal/gyarados.gif"),
        new Pokemon("Kyogre", ["Water"], 320, 90, [$originPulse, $iceBeam, $thunder, $waterSpout], "https://img.pokemondb.net/sprites/black-white/anim/normal/kyogre.gif"),
        new Pokemon("Blastoise", ["Water"], 300, 78, [$hydroPump, $iceBeam, $darkPulse, $flashCannon], "https://img.pokemondb.net/sprites/black-white/anim/normal/blastoise.gif"),
        new Pokemon("Mewtwo", ["Psychic"], 300, 130, [$psychic, $auraSphere, $shadowBall, $psyshock], "https://img.pokemondb.net/sprites/black-white/anim/normal/mewtwo.gif"),
        new Pokemon("Articuno", ["Ice", "Flying"], 280, 85, [$iceBeam, $hurricane, $freezeDry, $airSlash], "https://img.pokemondb.net/sprites/black-white/anim/normal/articuno.gif"),
        new Pokemon("Zapdos", ["Electric", "Flying"], 280, 100, [$thunderbolt, $hurricane, $heatWave, $drillPeck], "https://img.pokemondb.net/sprites/black-white/anim/normal/zapdos.gif"),
        new Pokemon("Pikachu", ["Electric"], 275, 90, [$thunderbolt, $volttackle, $quickAttack, $ironTail], "https://img.pokemondb.net/sprites/black-white/anim/normal/pikachu.gif"),
        new Pokemon("Arceus", ["Normal"], 340, 120, [$judgment, $hyperBeam, $earthquake, $shadowBall], "https://img.pokemondb.net/sprites/black-white/anim/normal/arceus.gif"),
        new Pokemon("Latias", ["Dragon", "Psychic"], 280, 110, [$dragonPulse, $psychic, $mistBall, $aerialAce], "https://img.pokemondb.net/sprites/black-white/anim/normal/latias.gif"),
        new Pokemon("Reshiram", ["Dragon", "Fire"], 300, 90, [$blueFlare, $dragonPulse, $fusionFlare, $ancientPower], "https://img.pokemondb.net/sprites/black-white/anim/normal/reshiram.gif"),
        new Pokemon("Zekrom", ["Dragon", "Electric"], 300, 90, [$boltStrike, $dragonClaw, $fusionBolt, $crossThunder], "https://img.pokemondb.net/sprites/black-white/anim/normal/zekrom.gif"),
        new Pokemon("Charizard", ["Fire", "Flying"], 290, 100, [$flamethrower, $airSlash, $dragonPulse, $fireBlast], "https://img.pokemondb.net/sprites/black-white/anim/normal/charizard.gif"),
        new Pokemon("Venusaur", ["Grass", "Poison"], 290, 80, [$solarBeam, $sludgeBomb, $earthPower, $seedBomb], "https://img.pokemondb.net/sprites/black-white/anim/normal/venusaur.gif"),
        new Pokemon("Sceptile", ["Grass"], 290, 120, [$leafBlade, $dragonPulse, $focusBlast, $xScissor], "https://img.pokemondb.net/sprites/black-white/anim/normal/sceptile.gif"),
        new Pokemon("Gengar", ["Ghost", "Poison"], 275, 110, [$shadowBall, $sludgeBomb, $focusBlast, $thunderbolt], "https://img.pokemondb.net/sprites/black-white/anim/normal/gengar.gif"),
        new Pokemon("Snorlax", ["Normal"], 400, 30, [$bodySlam, $earthquake, $crunch, $heavySlam], "https://img.pokemondb.net/sprites/black-white/anim/normal/snorlax.gif"),
        new Pokemon("Tyranitar", ["Rock", "Dark"], 320, 61, [$stoneEdge, $crunch, $earthquake, $firePunch], "https://img.pokemondb.net/sprites/black-white/anim/normal/tyranitar.gif"),
        new Pokemon("Hitmonlee", ["Fighting"], 265, 87, [$highJumpKick, $stoneEdge, $poisonJab, $blazeKick], "https://img.pokemondb.net/sprites/black-white/anim/normal/hitmonlee.gif")
    ];
}
// Handle New Game or Reset
if (isset($_POST['new_game']) || isset($_POST['reset'])) {
    // Completely destroy and restart session
    session_destroy();
    session_start();
    $_SESSION = array();
    
    // Reinitialize everything
    $_SESSION['pokemon_pool'] = createPokemonPool();
    $_SESSION['player1_team'] = [];
    $_SESSION['player2_team'] = [];
    $_SESSION['player1_name'] = 'Player 1';
    $_SESSION['player2_name'] = 'Player 2';
    $_SESSION['battle'] = null;
    
    // Clear any output buffer
    ob_clean();
}

// Initialize Pokemon pool if not set
if (!isset($_SESSION['pokemon_pool'])) {
    $_SESSION['pokemon_pool'] = createPokemonPool();
    $_SESSION['player1_team'] = [];
    $_SESSION['player2_team'] = [];
    $_SESSION['player1_name'] = 'Player 1';
    $_SESSION['player2_name'] = 'Player 2';
    $_SESSION['battle'] = null;
}

// Handle team selection
if (isset($_POST['select_team'])) {
    $player = $_POST['player'];
    $selectedPokemon = isset($_POST['pokemon']) ? $_POST['pokemon'] : [];
    
    if ($player === 'player1') {
        $_SESSION['player1_name'] = isset($_POST['player1_name']) ? htmlspecialchars($_POST['player1_name']) : 'Player 1';
        
        // Clear existing team and add new selections
        $_SESSION['player1_team'] = [];
        foreach ($_SESSION['pokemon_pool'] as $index => $pokemon) {
            if (in_array($index, $selectedPokemon) && count($_SESSION['player1_team']) < 3) {
                $_SESSION['player1_team'][] = $pokemon;
            }
        }
        
        // Remove selected Pokemon from pool for player 2
        $tempPool = [];
        foreach ($_SESSION['pokemon_pool'] as $index => $pokemon) {
            if (!in_array($index, $selectedPokemon)) {
                $tempPool[] = $pokemon;
            }
        }
        $_SESSION['pokemon_pool'] = $tempPool;
        
    } else {
        $_SESSION['player2_name'] = isset($_POST['player2_name']) ? htmlspecialchars($_POST['player2_name']) : 'Player 2';
        
        // Clear existing team and add new selections
        $_SESSION['player2_team'] = [];
        foreach ($_SESSION['pokemon_pool'] as $index => $pokemon) {
            if (in_array($index, $selectedPokemon) && count($_SESSION['player2_team']) < 3) {
                $_SESSION['player2_team'][] = $pokemon;
            }
        }
        
        // Create battle instance if both teams are complete
        if (count($_SESSION['player1_team']) === 3 && count($_SESSION['player2_team']) === 3) {
            $_SESSION['battle'] = new PokemonBattle(
                $_SESSION['player1_name'], 
                $_SESSION['player2_name'], 
                $_SESSION['player1_team'], 
                $_SESSION['player2_team']
            );
        }
    }
}

// Start battle manually if requested
if (isset($_POST['start_battle']) && 
    isset($_SESSION['player1_team']) && 
    isset($_SESSION['player2_team']) && 
    count($_SESSION['player1_team']) === 3 && 
    count($_SESSION['player2_team']) === 3) {
    
    $_SESSION['battle'] = new PokemonBattle(
        $_SESSION['player1_name'], 
        $_SESSION['player2_name'], 
        $_SESSION['player1_team'], 
        $_SESSION['player2_team']
    );
}

// Get battle if exists
$battle = isset($_SESSION['battle']) ? $_SESSION['battle'] : null;

// Process actions if battle exists and is not over
$result = null;
$showAnimation = false;
$targetPlayer = null;

if ($battle && !$battle->isGameOver() && isset($_POST['action'])) {
    $player = $_POST['player'];
    $actionType = $_POST['action'];
    
    switch ($actionType) {
        case 'attack':
            $attackIndex = (int)$_POST['attack_index'];
            $action = new AttackAction($attackIndex);
            break;
        case 'switch':
            $pokemonIndex = (int)$_POST['pokemon_index'];
            $action = new SwitchAction($pokemonIndex);
            break;
        case 'item':
            $action = new ItemAction();
            break;
        default:
            $action = null;
    }
    
    if ($action) {
        $result = $battle->executeTurn($player, $action);
        
        if ($actionType === 'attack' && isset($result['show_animation']) && $result['show_animation']) {
            $showAnimation = true;
            $targetPlayer = $result['target_player'];
        }
    }
}

// End output buffering and send all output
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokemon Battle Arena</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(
            45deg,
            #300000,
            #302000,
            #303000,
            #203000,
            #003000,
            #003020,
            #003030,
            #001030,
            #000030,
            #200030,
            #300030,
            #300020
);
            background-size: 1200% 1200%;
            animation: rgbMove 30s ease infinite;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        @keyframes rgbMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        h1 {
            text-align: center;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            color: #ffcc00;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffcc00;
        }
        
        .new-game-btn {
            padding: 10px 20px;
            background: #ffcc00;
            color: #333;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .new-game-btn:hover {
            background: #ffd633;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .music-controls {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 15px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }
        
        .music-btn {
            background: #ffcc00;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .music-btn:hover {
            background: #ffd633;
            transform: scale(1.1);
        }
        
        .volume-slider {
            width: 100px;
            cursor: pointer;
        }
        
        .pokemon-image-container {
            position: relative;
            display: inline-block;
        }
        
        .attack-animation {
            position: absolute;
            top: 100%;
            left: 100%;
            transform: translate(-50%, -50%);
            z-index: 100;
            pointer-events: none;
        }
        
        .attack-animation.hidden {
            display: none;
        }
        
        .attack-image {
            width: 100px;
            height: 100px;
            animation: attackEffect 1s ease-in-out forwards;
        }
        
        @keyframes attackEffect {
            0% { 
                transform: translate(-50%, -50%) scale(0.5); 
                opacity: 0; 
            }
            50% { 
                transform: translate(-50%, -50%) scale(1.2); 
                opacity: 1; 
            }
            100% { 
                transform: translate(-50%, -50%) scale(1); 
                opacity: 0;
            }
        }
        
        .screen-shake {
            animation: screenShake 0.5s ease-in-out;
        }
        
        @keyframes screenShake {
            0%, 100% { transform: translateX(0) translateY(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px) translateY(-3px); }
            20%, 40%, 60%, 80% { transform: translateX(5px) translateY(3px); }
        }
        
        .pokemon-shake {
            animation: pokemonShake 0.5s ease-in-out;
        }
        
        @keyframes pokemonShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            50% { transform: translateX(8px); }
            75% { transform: translateX(-8px); }
        }
        
        .damage-flash {
            animation: damageFlash 0.3s ease-in-out;
        }
        
        @keyframes damageFlash {
            0%, 100% { background-color: transparent; }
            50% { background-color: rgba(255, 0, 0, 0.3); }
        }
        
        .attack-loading {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            z-index: 1001;
            display: none;
        }
        
        .fainted {
            border: 3px solid #f44336;
            box-shadow: 0 0 20px rgba(244, 67, 54, 0.7);
            position: relative;
            overflow: hidden;
        }

        .fainted::before {
            content: "FAINTED";
            position: absolute;
            top: 15%;
            left: 12.5%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 32px;
            font-weight: bold;
            color: rgba(244, 67, 54, 0.5);
            z-index: 1;
            pointer-events: none;
        }

        .fainted .pokemon-image {
            filter: grayscale(0.8);
            opacity: 0.9;
        }

        .fainted2 {
            border: 3px solid #f44336 !important;
            box-shadow: 0 0 20px rgba(244, 67, 54, 0.7);
            position: relative;
            overflow: hidden;
        }

        .fainted2::before {
            content: "FAINTED";
            position: absolute;
            top: 42.5%;
            left: 12.5%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 32px;
            font-weight: bold;
            color: rgba(244, 67, 54, 0.5);
            z-index: 1;
            pointer-events: none;
        }

        .fainted2 .pokemon-image {
            filter: grayscale(0.8);
            opacity: 0.9;
        }

        .switch-option.disabled {
            border: 2px solid #f44336 !important;
            background: rgba(244, 67, 54, 0.2) !important;
            position: relative;
            overflow: hidden;
        }

        .switch-option.disabled::before {
            content: "FAINTED";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 20px;
            font-weight: bold;
            color: rgba(244, 67, 54, 0.15);
            z-index: 1;
            pointer-events: none;
        }

        .switch-option.disabled .switch-pokemon-image {
            filter: grayscale(0.8);
            opacity: 0.9;
        }
        
        .team-selection {
            text-align: center;
        }
        
        .player-form {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .player-input {
            padding: 10px;
            margin: 10px 0;
            width: 200px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .pokemon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .pokemon-option {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 3px solid transparent;
        }
        
        .pokemon-option:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }
        
        .pokemon-option.selected {
            background: rgba(76, 175, 80, 0.2);
            border: 3px solid #4caf50;
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.5);
        }
        
        .pokemon-option input {
            display: none; /* Hide the actual checkbox */
        }
        
        .pokemon-option img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        
        .pokemon-name {
            font-weight: bold;
            margin: 10px 0 5px;
        }
        
        .pokemon-types {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-bottom: 5px;
            flex-wrap: wrap;
        }
        
        .pokemon-type {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .type-normal { background: #a8a878; color: white; }
        .type-fire { background: #f08030; color: white; }
        .type-water { background: #6890f0; color: white; }
        .type-grass { background: #78c850; color: white; }
        .type-electric { background: #f8d030; color: black; }
        .type-ice { background: #98d8d8; color: black; }
        .type-fighting { background: #c03028; color: white; }
        .type-poison { background: #a040a0; color: white; }
        .type-ground { background: #e0c068; color: black; }
        .type-flying { background: #a890f0; color: white; }
        .type-psychic { background: #f85888; color: white; }
        .type-bug { background: #a8b820; color: white; }
        .type-rock { background: #b8a038; color: white; }
        .type-ghost { background: #705898; color: white; }
        .type-dragon { background: #7038f8; color: white; }
        .type-dark { background: #705848; color: white; }
        .type-steel { background: #b8b8d0; color: black; }
        .type-fairy { background: #ee99ac; color: white; }
        
        .select-btn {
            padding: 12px 30px;
            background: #ffcc00;
            color: #333;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .select-btn:hover {
            background: #ffd633;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .select-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .instruction {
            text-align: center;
            margin: 20px 0;
            font-size: 1.2rem;
            color: #ffcc00;
        }
        
        .waiting-message {
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            margin: 20px 0;
        }
        
        .battle-field {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .player-area {
            flex: 1;
            min-width: 300px;
            margin: 0 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            position: relative;
        }
        
        .player-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .player-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffcc00;
        }
        
        .current-turn {
            background: rgba(255, 204, 0, 0.2);
            box-shadow: 0 0 15px rgba(255, 204, 0, 0.5);
        }
        
        .pokemon-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
            transition: all 0.3s;
        }
        
        .active-pokemon {
            border: 3px solid #ffcc00;
            background: rgba(255, 204, 0, 0.1);
        }
        
        .pokemon-display {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .pokemon-image {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
            transition: all 0.3s;
        }
        
        .pokemon-info {
            flex: 1;
        }
        
        .health-bar {
            height: 20px;
            background: #333;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
            position: relative;
            z-index: 2; /* Ensure it appears above the FAINTED watermark */
        }
        
        .health-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s;
        }
        
        .health-high { background: #4caf50; }
        .health-medium { background: #ff9800; }
        .health-low { background: #f44336; }
        .health-zero { 
            background: #9e9e9e; 
            width: 100% !important;
        }
        
        .health-text {
            text-align: center;
            font-size: 0.9rem;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        
        .attacks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }
        
        .attack-btn {
            padding: 10px;
            background: rgba(255, 204, 0, 0.3);
            border: 1px solid #ffcc00;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .attack-btn:hover:not(:disabled) {
            background: rgba(255, 204, 0, 0.5);
        }
        
        .attack-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .switch-section {
            margin-top: 15px;
        }
        
        .switch-title {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: #ffcc00;
        }
        
        .switch-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .switch-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: rgba(76, 175, 80, 0.3);
            border: 1px solid #4caf50;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .switch-option:hover:not(:disabled) {
            background: rgba(76, 175, 80, 0.5);
        }
        
        .switch-option:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .switch-form {
            display: inline;
        }
        
        .switch-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            text-align: left;
        }
        
        .switch-btn:disabled {
            cursor: not-allowed;
        }
        
        .switch-pokemon-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .switch-pokemon-image {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .item-section {
            margin-top: 15px;
        }
        
        .item-btn {
            width: 100%;
            padding: 10px;
            background: #2196f3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .item-btn:hover:not(:disabled) {
            background: #42a5f5;
            transform: translateY(-2px);
        }
        
        .item-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .battle-log {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            height: 200px;
            overflow-y: auto;
        }
        
        .log-title {
            text-align: center;
            margin-bottom: 10px;
            color: #ffcc00;
            font-weight: bold;
        }
        
        .log-entry {
            padding: 5px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .game-over {
            text-align: center;
            padding: 30px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            margin-top: 20px;
        }
        
        .winner {
            font-size: 2rem;
            color: #ffcc00;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .reset-btn {
            padding: 12px 30px;
            background: #ffcc00;
            color: #333;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .reset-btn:hover {
            background: #ffd633;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .battle-field {
                flex-direction: column;
            }
            
            .player-area {
                margin: 10px 0;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
            }
            
            .pokemon-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .music-controls {
                bottom: 10px;
                right: 10px;
                padding: 8px 12px;
            }
            
            .volume-slider {
                width: 80px;
            }
            
            .attack-image {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Background Music -->
    <audio id="battleMusic" loop>
        <source src="music/POKEMONBATTLESOUND.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    
    <!-- Music Controls -->
    <div class="music-controls" id="musicControls" style="display: none;">
        <button class="music-btn" id="playPauseBtn">⏸️</button>
        <input type="range" id="volumeSlider" class="volume-slider" min="0" max="1" step="0.1" value="0.5">
    </div>

    <!-- Attack Loading Indicator -->
    <div class="attack-loading" id="attackLoading">Attacking...</div>

    <div class="container">
        <div class="header">
            <h1>Pokemon Battle Arena</h1>
            <form method="post">
                <button type="submit" name="new_game" class="new-game-btn">New Game</button>
            </form>
        </div>
        
        <?php if (!$battle): ?>
            <!-- Team Selection Phase -->
            <div class="team-selection">
                <?php if (!isset($_SESSION['player1_team']) || count($_SESSION['player1_team']) < 3): ?>
                    <!-- Player 1 Team Selection -->
                    <h2>Player 1: Choose Your Team</h2>
                    <p class="instruction">Select 3 Pokemon for your team</p>
                    
                    <form method="post" class="player-form" id="player1Form">
                        <input type="text" name="player1_name" class="player-input" placeholder="Player 1 Name" value="<?php echo $_SESSION['player1_name']; ?>" required>
                        
                        <div class="pokemon-grid" id="player1Grid">
                            <?php foreach ($_SESSION['pokemon_pool'] as $index => $pokemon): ?>
                                <div class="pokemon-option" data-index="<?php echo $index; ?>">
                                    <input type="checkbox" name="pokemon[]" value="<?php echo $index; ?>">
                                    <img src="<?php echo $pokemon->getImage(); ?>" alt="<?php echo $pokemon->getName(); ?>">
                                    <div class="pokemon-name"><?php echo $pokemon->getName(); ?></div>
                                    <div class="pokemon-types">
                                        <?php foreach ($pokemon->getTypes() as $type): ?>
                                            <div class="pokemon-type type-<?php echo strtolower($type); ?>">
                                                <?php echo $type; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div>HP: <?php echo $pokemon->getMaxHealth(); ?></div>
                                    <div>Speed: <?php echo $pokemon->getSpeed(); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <input type="hidden" name="player" value="player1">
                        <button type="submit" name="select_team" class="select-btn" id="player1Submit" disabled>Select Team</button>
                    </form>
                    
                <?php elseif (!isset($_SESSION['player2_team']) || count($_SESSION['player2_team']) < 3): ?>
                    <!-- Player 2 Team Selection -->
                    <h2>Player 2: Choose Your Team</h2>
                    <p class="instruction">Select 3 Pokemon for your team</p>
                    
                    <form method="post" class="player-form" id="player2Form">
                        <input type="text" name="player2_name" class="player-input" placeholder="Player 2 Name" value="<?php echo $_SESSION['player2_name']; ?>" required>
                        
                        <div class="pokemon-grid" id="player2Grid">
                            <?php foreach ($_SESSION['pokemon_pool'] as $index => $pokemon): ?>
                                <div class="pokemon-option" data-index="<?php echo $index; ?>">
                                    <input type="checkbox" name="pokemon[]" value="<?php echo $index; ?>">
                                    <img src="<?php echo $pokemon->getImage(); ?>" alt="<?php echo $pokemon->getName(); ?>">
                                    <div class="pokemon-name"><?php echo $pokemon->getName(); ?></div>
                                    <div class="pokemon-types">
                                        <?php foreach ($pokemon->getTypes() as $type): ?>
                                            <div class="pokemon-type type-<?php echo strtolower($type); ?>">
                                                <?php echo $type; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div>HP: <?php echo $pokemon->getMaxHealth(); ?></div>
                                    <div>Speed: <?php echo $pokemon->getSpeed(); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <input type="hidden" name="player" value="player2">
                        <button type="submit" name="select_team" class="select-btn" id="player2Submit" disabled>Select Team</button>
                    </form>
                    
                <?php else: ?>
                    <!-- Both teams selected, ready to start battle -->
                    <div class="waiting-message">
                        <h2>Teams Selected!</h2>
                        <p class="instruction">Ready to start the battle!</p>
                        <form method="post">
                            <button type="submit" name="start_battle" class="select-btn">Start Battle</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($battle->isGameOver()): ?>
            <!-- Game Over Screen -->
            <div class="game-over">
                <h2>Battle Over!</h2>
                <div class="winner">🏆 <?php echo $battle->getWinner(); ?> wins! 🏆</div>
                <form method="post">
                    <button type="submit" name="reset" class="reset-btn">New Battle</button>
                </form>
            </div>
            
        <?php else: ?>
            <!-- Battle Screen -->
            <div class="battle-field">
                <!-- Player 1 Area -->
                <div class="player-area <?php echo $battle->getCurrentTurn() === $battle->getPlayer1() ? 'current-turn' : ''; ?>" id="player1-area">
                    <div class="player-header">
                        <div class="player-name"><?php echo $battle->getPlayer1(); ?></div>
                        <div><?php echo $battle->getCurrentTurn() === $battle->getPlayer1() ? 'Your Turn!' : ''; ?></div>
                    </div>
                    
                    <?php foreach ($battle->getPlayer1Pokemon() as $index => $pokemon): ?>
                        <div class="pokemon-card 
                            <?php echo $index === 0 ? 'active-pokemon' : ''; ?> 
                            <?php echo $pokemon->isFainted() ? ($index === 0 ? 'fainted' : 'fainted2') : ''; ?>" 
                            id="player1-pokemon-<?php echo $index; ?>">
                            <div class="pokemon-display">
                                <div class="pokemon-image-container">
                                    <img src="<?php echo $pokemon->getImage(); ?>" alt="<?php echo $pokemon->getName(); ?>" class="pokemon-image" id="player1-sprite-<?php echo $index; ?>">
                                    <!-- Animation container for player 1 - positioned directly on sprite -->
                                    <div class="attack-animation hidden" id="animation-player1">
                                        <img src="https://www.spriters-resource.com/media/asset_icons/173/176527.gif?updated=1755486559" class="attack-image" alt="Attack Animation">
                                    </div>
                                </div>
                                <div class="pokemon-info">
                                    <div class="pokemon-name"><?php echo $pokemon->getName(); ?></div>
                                    <div class="pokemon-types">
                                        <?php foreach ($pokemon->getTypes() as $type): ?>
                                            <div class="pokemon-type type-<?php echo strtolower($type); ?>">
                                                <?php echo $type; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="health-bar">
                                        <?php 
                                        $healthPercent = ($pokemon->getHealth() / $pokemon->getMaxHealth()) * 100;
                                        $healthClass = 'health-high';
                                        if ($healthPercent < 50) $healthClass = 'health-medium';
                                        if ($healthPercent < 25) $healthClass = 'health-low';
                                        if ($pokemon->isFainted()) $healthClass = 'health-zero';
                                        ?>
                                        <div class="health-fill <?php echo $healthClass; ?>" style="width: <?php echo $pokemon->isFainted() ? '100' : $healthPercent; ?>%"></div>
                                    </div>
                                    <div class="health-text">
                                        HP: <?php echo $pokemon->getHealth(); ?>/<?php echo $pokemon->getMaxHealth(); ?>
                                        <?php if ($pokemon->isFainted()): ?>
                                            <span style="color: #f44336; font-weight: bold;"> - FAINTED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($index === 0): ?>
                                <div class="attacks">
                                    <?php foreach ($pokemon->getAttacks() as $attackIndex => $attack): ?>
                                        <form method="post" class="attack-form" onsubmit="return handleAttack(this, '<?php echo $battle->getPlayer1(); ?>', <?php echo $attackIndex; ?>)">
                                            <input type="hidden" name="player" value="<?php echo $battle->getPlayer1(); ?>">
                                            <input type="hidden" name="action" value="attack">  
                                            <input type="hidden" name="attack_index" value="<?php echo $attackIndex; ?>">
                                            <button type="submit" class="attack-btn" 
                                                    <?php echo $battle->getCurrentTurn() !== $battle->getPlayer1() || $pokemon->isFainted() ? 'disabled' : ''; ?>>
                                                <?php echo $attack->getName(); ?> (<?php echo $attack->getPower(); ?>)
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Switch Pokemon Section -->
                                <div class="switch-section">
                                    <div class="switch-title">Switch Pokemon:</div>
                                    <div class="switch-options">
                                        <?php foreach ($battle->getPlayer1Pokemon() as $switchIndex => $switchPokemon): ?>
                                            <?php if ($switchIndex !== 0): ?>
                                                <div class="switch-option <?php echo $switchPokemon->isFainted() ? 'disabled' : ''; ?>">
                                                    <div class="switch-pokemon-info">
                                                        <img src="<?php echo $switchPokemon->getImage(); ?>" 
                                                             alt="<?php echo $switchPokemon->getName(); ?>" 
                                                             class="switch-pokemon-image 
                                                                    <?php echo $switchPokemon->isFainted() ? 
                                                                        ($switchIndex === 0 ? 'fainted' : 'fainted2') : ''; ?>">
                                                        <span><?php echo $switchPokemon->getName(); ?></span>
                                                        <div class="pokemon-types">
                                                            <?php foreach ($switchPokemon->getTypes() as $type): ?>
                                                                <div class="pokemon-type type-<?php echo strtolower($type); ?>">
                                                                    <?php echo $type; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <span>HP: <?php echo $switchPokemon->getHealth(); ?>/<?php echo $switchPokemon->getMaxHealth(); ?></span>
                                                        <?php if ($switchPokemon->isFainted()): ?>
                                                            <span style="color: #f44336; font-weight: bold;"> - FAINTED</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <form method="post" class="switch-form">
                                                        <input type="hidden" name="player" value="<?php echo $battle->getPlayer1(); ?>">
                                                        <input type="hidden" name="action" value="switch">
                                                        <input type="hidden" name="pokemon_index" value="<?php echo $switchIndex; ?>">
                                                        <button type="submit" class="switch-btn" 
                                                                <?php echo $battle->getCurrentTurn() !== $battle->getPlayer1() || $switchPokemon->isFainted() ? 'disabled' : ''; ?>>
                                                            Switch
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Use Item Section -->
                                <div class="item-section">
                                    <form method="post">
                                        <input type="hidden" name="player" value="<?php echo $battle->getPlayer1(); ?>">
                                        <input type="hidden" name="action" value="item">
                                        <button type="submit" class="item-btn"
                                                <?php echo $battle->getCurrentTurn() !== $battle->getPlayer1() || $pokemon->isFainted() ? 'disabled' : ''; ?>>
                                            Use Potion (+70 HP)
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Player 2 Area -->
                <div class="player-area <?php echo $battle->getCurrentTurn() === $battle->getPlayer2() ? 'current-turn' : ''; ?>" id="player2-area">
                    <div class="player-header">
                        <div class="player-name"><?php echo $battle->getPlayer2(); ?></div>
                        <div><?php echo $battle->getCurrentTurn() === $battle->getPlayer2() ? 'Your Turn!' : ''; ?></div>
                    </div>
                    
                    <?php foreach ($battle->getPlayer2Pokemon() as $index => $pokemon): ?>
                        <div class="pokemon-card 
                            <?php echo $index === 0 ? 'active-pokemon' : ''; ?> 
                            <?php echo $pokemon->isFainted() ? ($index === 0 ? 'fainted' : 'fainted2') : ''; ?>" 
                            id="player2-pokemon-<?php echo $index; ?>">
                            <div class="pokemon-display">
                                <div class="pokemon-image-container">
                                    <img src="<?php echo $pokemon->getImage(); ?>" alt="<?php echo $pokemon->getName(); ?>" class="pokemon-image" id="player2-sprite-<?php echo $index; ?>">
                                    <!-- Animation container for player 2 - positioned directly on sprite -->
                                    <div class="attack-animation hidden" id="animation-player2">
                                        <img src="https://www.spriters-resource.com/media/asset_icons/173/176527.gif?updated=1755486559" class="attack-image" alt="Attack Animation">
                                    </div>
                                </div>
                                <div class="pokemon-info">
                                    <div class="pokemon-name"><?php echo $pokemon->getName(); ?></div>
                                    <div class="pokemon-types">
                                        <?php foreach ($pokemon->getTypes() as $type): ?>
                                            <div class="pokemon-type type-<?php echo strtolower($type); ?>">
                                                <?php echo $type; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="health-bar">
                                        <?php 
                                        $healthPercent = ($pokemon->getHealth() / $pokemon->getMaxHealth()) * 100;
                                        $healthClass = 'health-high';
                                        if ($healthPercent < 50) $healthClass = 'health-medium';
                                        if ($healthPercent < 25) $healthClass = 'health-low';
                                        if ($pokemon->isFainted()) $healthClass = 'health-zero';
                                        ?>
                                        <div class="health-fill <?php echo $healthClass; ?>" style="width: <?php echo $pokemon->isFainted() ? '100' : $healthPercent; ?>%"></div>
                                    </div>
                                    <div class="health-text">
                                        HP: <?php echo $pokemon->getHealth(); ?>/<?php echo $pokemon->getMaxHealth(); ?>
                                        <?php if ($pokemon->isFainted()): ?>
                                            <span style="color: #f44336; font-weight: bold;"> - FAINTED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($index === 0): ?>
                                <div class="attacks">
                                    <?php foreach ($pokemon->getAttacks() as $attackIndex => $attack): ?>
                                        <form method="post" class="attack-form" onsubmit="return handleAttack(this, '<?php echo $battle->getPlayer2(); ?>', <?php echo $attackIndex; ?>)">
                                            <input type="hidden" name="player" value="<?php echo $battle->getPlayer2(); ?>">
                                            <input type="hidden" name="action" value="attack">
                                            <input type="hidden" name="attack_index" value="<?php echo $attackIndex; ?>">
                                            <button type="submit" class="attack-btn" 
                                                    <?php echo $battle->getCurrentTurn() !== $battle->getPlayer2() || $pokemon->isFainted() ? 'disabled' : ''; ?>>
                                                <?php echo $attack->getName(); ?> (<?php echo $attack->getPower(); ?>)
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Switch Pokemon Section -->
                                <div class="switch-section">
                                    <div class="switch-title">Switch Pokemon:</div>
                                    <div class="switch-options">
                                        <?php foreach ($battle->getPlayer2Pokemon() as $switchIndex => $switchPokemon): ?>
                                            <?php if ($switchIndex !== 0): ?>
                                                <div class="switch-option <?php echo $switchPokemon->isFainted() ? 'disabled' : ''; ?>">
                                                    <div class="switch-pokemon-info">
                                                        <img src="<?php echo $switchPokemon->getImage(); ?>" 
                                                             alt="<?php echo $switchPokemon->getName(); ?>" 
                                                             class="switch-pokemon-image 
                                                                    <?php echo $switchPokemon->isFainted() ? 
                                                                        ($switchIndex === 0 ? 'fainted' : 'fainted2') : ''; ?>">
                                                        <span><?php echo $switchPokemon->getName(); ?></span>
                                                        <div class="pokemon-types">
                                                            <?php foreach ($switchPokemon->getTypes() as $type): ?>
                                                                <div class="pokemon-type type-<?php echo strtolower($type); ?>">
                                                                    <?php echo $type; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <span>HP: <?php echo $switchPokemon->getHealth(); ?>/<?php echo $switchPokemon->getMaxHealth(); ?></span>
                                                        <?php if ($switchPokemon->isFainted()): ?>
                                                            <span style="color: #f44336; font-weight: bold;"> - FAINTED</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <form method="post" class="switch-form">
                                                        <input type="hidden" name="player" value="<?php echo $battle->getPlayer2(); ?>">
                                                        <input type="hidden" name="action" value="switch">
                                                        <input type="hidden" name="pokemon_index" value="<?php echo $switchIndex; ?>">
                                                        <button type="submit" class="switch-btn" 
                                                                <?php echo $battle->getCurrentTurn() !== $battle->getPlayer2() || $switchPokemon->isFainted() ? 'disabled' : ''; ?>>
                                                            Switch
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Use Item Section -->
                                <div class="item-section">
                                    <form method="post">
                                        <input type="hidden" name="player" value="<?php echo $battle->getPlayer2(); ?>">
                                        <input type="hidden" name="action" value="item">
                                        <button type="submit" class="item-btn"
                                                <?php echo $battle->getCurrentTurn() !== $battle->getPlayer2() || $pokemon->isFainted() ? 'disabled' : ''; ?>>
                                            Use Potion (+70 HP)
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="battle-log">
                <div class="log-title">Battle Log</div>
                <?php foreach (array_reverse($battle->getBattleLog()) as $logEntry): ?>
                    <div class="log-entry"><?php echo $logEntry; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Music functionality
        document.addEventListener('DOMContentLoaded', function() {
            const battleMusic = document.getElementById('battleMusic');
            const playPauseBtn = document.getElementById('playPauseBtn');
            const volumeSlider = document.getElementById('volumeSlider');
            const musicControls = document.getElementById('musicControls');
            
            // Only show music controls and play music during battle
            <?php if ($battle && !$battle->isGameOver()): ?>
                // Show music controls during battle
                musicControls.style.display = 'flex';
                
                // Set initial volume
                battleMusic.volume = volumeSlider.value;
                
                // Try to play music automatically when battle starts
                const playMusic = () => {
                    battleMusic.play().catch(error => {
                        console.log('Autoplay prevented, waiting for user interaction');
                        // If autoplay is blocked, change button to play and wait for user click
                        playPauseBtn.textContent = '▶️';
                    });
                };
                
                // Try to play immediately
                playMusic();
                
                // Also try to play when user interacts with the page
                document.addEventListener('click', function() {
                    if (battleMusic.paused) {
                        playMusic();
                    }
                }, { once: true });
                
                // Play/Pause button functionality
                playPauseBtn.addEventListener('click', function() {
                    if (battleMusic.paused) {
                        battleMusic.play();
                        playPauseBtn.textContent = '⏸️';
                    } else {
                        battleMusic.pause();
                        playPauseBtn.textContent = '▶️';
                    }
                });
                
                // Volume slider functionality
                volumeSlider.addEventListener('input', function() {
                    battleMusic.volume = this.value;
                });
                
                // Loop the music when it ends
                battleMusic.addEventListener('ended', function() {
                    battleMusic.currentTime = 0;
                    battleMusic.play();
                });
            <?php endif; ?>
            
            // Handle attack animations with screen shake
            function showAttackAnimation(targetPlayer) {
                const animationElement = document.getElementById(`animation-${targetPlayer}`);
                const pokemonCard = document.getElementById(`${targetPlayer}-pokemon-0`);
                const pokemonSprite = document.getElementById(`${targetPlayer}-sprite-0`);
                
                if (animationElement) {
                    animationElement.classList.remove('hidden');
                    
                    // Remove animation after 1 second
                    setTimeout(() => {
                        animationElement.classList.add('hidden');
                    }, 1000);
                }
                
                // Add screen shake effect to the opponent's Pokemon card
                if (pokemonCard) {
                    pokemonCard.classList.add('screen-shake');
                    setTimeout(() => {
                        pokemonCard.classList.remove('screen-shake');
                    }, 500);
                }
                
                // Add Pokemon sprite shake effect
                if (pokemonSprite) {
                    pokemonSprite.classList.add('pokemon-shake');
                    setTimeout(() => {
                        pokemonSprite.classList.remove('pokemon-shake');
                    }, 500);
                }
                
                // Add damage flash effect
                if (pokemonCard) {
                    pokemonCard.classList.add('damage-flash');
                    setTimeout(() => {
                        pokemonCard.classList.remove('damage-flash');
                    }, 300);
                }
            }
            
            // Global function to handle attack form submission
            window.handleAttack = function(form, player, attackIndex) {
                const attackLoading = document.getElementById('attackLoading');
                const attackBtn = form.querySelector('.attack-btn');
                
                // Show loading and disable button
                attackLoading.style.display = 'block';
                attackBtn.disabled = true;
                
                // Determine target player for animation
                const targetPlayer = (player === '<?php echo $battle ? $battle->getPlayer1() : ''; ?>') ? 
                    'player2' : 'player1';
                
                // Show animation immediately with screen shake
                showAttackAnimation(targetPlayer);
                
                // Submit the form after a short delay to ensure animation plays
                setTimeout(() => {
                    form.submit();
                }, 100);
                
                return false; // Prevent default form submission
            };
            
            // If there was a PHP-triggered animation (for page refresh cases)
            <?php if ($showAnimation): ?>
                setTimeout(() => {
                    showAttackAnimation('<?php echo $targetPlayer === $battle->getPlayer1() ? 'player1' : 'player2'; ?>');
                }, 100);
            <?php endif; ?>
            
            // Team selection - click to select Pokemon
            const pokemonOptions = document.querySelectorAll('.pokemon-option');
            const submitButtons = document.querySelectorAll('.select-btn');
            
            if (pokemonOptions.length > 0) {
                let selectedPokemon = [];
                
                pokemonOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        const index = this.getAttribute('data-index');
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        
                        if (selectedPokemon.includes(index)) {
                            // Deselect
                            selectedPokemon = selectedPokemon.filter(i => i !== index);
                            this.classList.remove('selected');
                            checkbox.checked = false;
                        } else {
                            // Select if we have less than 3
                            if (selectedPokemon.length < 3) {
                                selectedPokemon.push(index);
                                this.classList.add('selected');
                                checkbox.checked = true;
                            }
                        }
                        
                        // Update submit button state
                        submitButtons.forEach(button => {
                            if (button) {
                                button.disabled = selectedPokemon.length !== 3;
                            }
                        });
                        
                        // Visual feedback for max selection
                        if (selectedPokemon.length >= 3) {
                            pokemonOptions.forEach(opt => {
                                if (!selectedPokemon.includes(opt.getAttribute('data-index'))) {
                                    opt.style.opacity = '0.5';
                                }
                            });
                        } else {
                            pokemonOptions.forEach(opt => {
                                opt.style.opacity = '1';
                            });
                        }
                    });
                });
                
                // Initially disable submit buttons
                submitButtons.forEach(button => {
                    if (button) {
                        button.disabled = true;
                    }
                });
            }
        });
    </script>
</body>
</html>