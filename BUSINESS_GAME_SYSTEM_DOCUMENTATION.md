# Business Simulation Game System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Game Architecture](#game-architecture)
3. [Time System](#time-system)
4. [Core Game Systems](#core-game-systems)
5. [Mathematical Models](#mathematical-models)
6. [Database Schema](#database-schema)
7. [API Endpoints](#api-endpoints)
8. [Game Mechanics](#game-mechanics)
9. [Admin Features](#admin-features)
10. [Company Features](#company-features)

## System Overview

The Business Simulation Game is an educational platform designed for student events where participants compete as companies in a simulated business environment. The system features:

- **Admin Panel**: System configuration and event management
- **Company Interface**: Player competition and business operations
- **Real-time Simulation**: 1 minute real-time = 1 game day
- **Multi-dimensional Competition**: R&D, production, marketing, finance, and operations

## Game Architecture

### System Layers
```
┌─────────────────────────────────────┐
│           Frontend (Svelte)         │
├─────────────────────────────────────┤
│         API Layer (Laravel)         │
├─────────────────────────────────────┤
│      Business Logic Services        │
├─────────────────────────────────────┤
│         Database (MySQL)            │
└─────────────────────────────────────┘
```

### Key Components
- **Laravel Backend**: RESTful API and business logic
- **Svelte Frontend**: Reactive UI components
- **Console Commands**: Game time progression and automation
- **Event System**: Dynamic game events and scenarios

## Time System

### Game Time Calculation
The game uses a timestamp-based system where:
- **Real Time**: 1 minute = 1 game day
- **Game Week**: Calculated from starting timestamp
- **Formula**: `Game Week = ⌊(Current Timestamp - Starting Timestamp) / 7⌋`

### Time Progression
```php
// Console command increments game time
$currentTimestamp = SettingsService::getCurrentTimestamp();
$gameWeek = floor(($currentTimestamp - $startingTimestamp) / 7);
```

### Game Time Loop
The system uses a console command (`game:time-loop`) that:
- **Increments game time** by configured speed (1 minute real-time = 1 game day)
- **Processes daily operations**: Technology research, purchases, sales, employee mood, production, machine reliability
- **Triggers weekly events** (every 7 days): Inventory costs, machine costs, supplier changes, sales generation
- **Triggers monthly events** (1st of month): Salaries and loan payments

## Core Game Systems

### 1. Research & Development (R&D)

#### Technology Tree Structure
- **Hierarchical Levels**: Technologies must be unlocked sequentially
- **Prerequisites**: All previous level technologies must be completed
- **Research Cost**: Configurable per technology
- **Research Time**: Days required for completion

#### Technology Model
```php
class Technology {
    public $level;           // Technology tier (0, 1, 2, ...)
    public $research_cost;   // Cost to research
    public $research_time_days; // Days needed
    public $name;            // Technology name
    public $description;     // Technology description
}
```

#### Unlock Formula
```
Can Unlock Level N = All Technologies Level (N-1) Completed
```

### 2. Product Management

#### Product Types
- **Raw Materials**: Basic resources (no technology required)
- **Components**: Intermediate products (require technology)
- **Finished Products**: Final goods (require technology + components)

#### Product Recipe System
```php
class ProductRecipe {
    public $product_id;      // Target product
    public $material_id;     // Required material
    public $quantity;        // Amount needed per unit
}
```

#### Production Formula
```
Total Materials Required = Recipe Quantity × Production Quantity
```

### 3. Demand and Pricing System

#### Demand Calculation
The system uses a sophisticated demand model with multiple factors:

**Base Demand Formula:**
```
Base Demand = Real Demand × (1 + Ad Market Impact)
```

**Price Elasticity Formula:**
```
Demand = Base Demand - (Elasticity Coefficient × Base Demand × Price Difference Ratio)
```

Where:
- `Price Difference Ratio = (Market Price - Company Price) / Market Price`
- `Elasticity Coefficient`: Configurable per product (typically 0.5 - 2.0)

**Final Demand Constraints:**
```
Final Demand = max(0, min(Max Demand, Calculated Demand))
```

#### Weekly Demand Generation
```php
// Normal distribution with randomness
$realDemand = $avgDemand + (normalRandom() * $standardDeviation);
$realDemand = max($minDemand, min($maxDemand, $realDemand));
```

#### Market Price System
- **Admin Set**: Base market price per week
- **Company Pricing**: Individual company sale prices
- **Competition**: Price wars and market positioning

### 4. Inventory Management

#### Storage Costs
- **Weekly Deduction**: Applied every 7 days
- **Formula**: `Storage Cost = Remaining Inventory × Unit Storage Cost`

#### Expiration System
- **Shelf Life**: Configurable per product
- **Expiration Check**: Daily inventory review
- **Formula**: `Expires At = Purchase Date + Shelf Life Days`

#### Inventory Movement Types
```php
enum MovementType {
    IN,           // Purchase/Production
    OUT,          // Sales/Consumption
    EXPIRED,      // Past shelf life
    DAMAGED,      // Event-based damage
    LOST          // System events
}
```

### 5. Geographic System

#### Countries
- **Import Controls**: Allow/block imports
- **Custom Duties**: Percentage-based tariffs
- **Formula**: `Total Cost = (Purchase Cost + Shipping) × (1 + Customs Rate)`

#### Wilayas (Regions)
- **Shipping Costs**: Min/Max/Real costs
- **Shipping Times**: Min/Max/Real delivery days
- **Dynamic Pricing**: Weekly cost fluctuations

#### Location-Based Pricing
```php
// Supplier location impact
if ($supplier->is_international) {
    $totalCost = ($purchaseCost + $shippingCost) * (1 + $country->customs_duties_rate);
} else {
    $totalCost = $purchaseCost + $shippingCost;
}
```

### 6. Employee Management

#### Employee Profile System
```php
class EmployeeProfile {
    public $min_salary_month;      // Minimum monthly salary
    public $max_salary_month;      // Maximum monthly salary
    public $min_recruitment_cost;  // Minimum hiring cost
    public $max_recruitment_cost;  // Maximum hiring cost
}
```

#### Employee Attributes
- **Efficiency Factor**: 0.0 - 2.0 (multiplier for machine speed)
- **Mood System**: 0.0 - 1.0 (affects performance and retention)
- **Salary Impact**: Higher salaries improve mood

#### Mood Decay System
```php
// Daily mood decay
$newMood = $currentMood - $moodDecayRate;
$newMood = max(0.0, $newMood);

// Resignation probability increases below 0.5
if ($newMood < 0.5) {
    $resignProbability = (0.5 - $newMood) * 2; // 0% to 100%
}
```

#### Machine Assignment
- **Profile Matching**: Employee profile must match machine requirements
- **Efficiency Impact**: `Real Speed = Machine Speed × Employee Efficiency`
- **Constraints**: Speed stays within machine min/max bounds

### 7. Financial System

#### Banking and Loans
```php
class Loan {
    public $amount;              // Principal amount
    public $duration_months;     // Loan term
    public $interest_rate;       // Annual interest rate
    public $monthly_payment;     // Calculated payment
}
```

#### Loan Calculations
```php
// Simple interest calculation (not compound)
$totalAmount = $amount * (1 + $interest_rate);
$monthlyPayment = $totalAmount / $duration_months;

// Auto-borrowing for insufficient funds
if ($company->funds < $monthlyPayment) {
    $randomBank = Bank::inRandomOrder()->first();
    self::borrowMoney($company, $randomBank, $monthlyPayment, "existing monthly loan payments");
}
```

#### Payment Schedules
- **Monthly (1st of month)**: 
  - Employee salaries
  - Loan payments
- **Weekly (every 7 days)**:
  - Inventory storage costs
  - Machine operation costs
  - Supplier price fluctuations
  - Wilaya shipping cost changes
  - Sales generation

#### Auto-Financing Triggers
- **Monthly Loan Payments**: Automatic deduction with auto-borrowing
- **Weekly Costs**: Inventory and machine operation charges
- **Employee Salaries**: Monthly payroll
- **Insufficient Funds**: Automatic borrowing from random banks

### 8. Machine Management

#### Machine Attributes
```php
class Machine {
    public $cost_to_acquire;        // Purchase price
    public $operations_cost;        // Daily operating cost
    public $quality_factor;         // Output quality multiplier
    public $carbon_footprint;       // Environmental impact
    public $min_speed, $max_speed;  // Production speed range
    public $reliability_decay_days; // Maintenance frequency
}
```

#### Machine Setup and Operation
```php
// PERT distribution for machine characteristics
$speed = calculatePertValue($minSpeed, $avgSpeed, $maxSpeed);
$maintenanceCost = calculatePertValue($minMaintenanceCost, $avgMaintenanceCost, $maxMaintenanceCost);

// Quality-adjusted output
$actualOutput = $baseOutput × $qualityFactor × $employeeEfficiency;
```

#### Maintenance System
- **Reliability Decay**: Daily reliability reduction
- **Maintenance Triggers**: When reliability drops below threshold
- **PERT Distribution**: Variable maintenance costs and times
- **Formula**: `Current Reliability = Initial Reliability - (Days × Decay Rate)`

### 9. Marketing and Advertising

#### Advertisement Impact
```php
class Advertiser {
    public $min_price, $max_price, $real_price;
    public $min_market_impact_percentage;
    public $max_market_impact_percentage;
    public $duration_days;
}
```

#### Market Impact Calculation
```php
// Ad market impact on demand
$adImpact = $advertiser->real_market_impact_percentage;
$boostedDemand = $baseDemand × (1 + $adImpact);
```

### 10. Production System

#### Production Orders
```php
class ProductionOrder {
    public $quantity;                    // Units to produce
    public $time_to_complete;            // Days needed
    public $quality_factor;              // Output quality
    public $employee_efficiency_factor;  // Worker efficiency
    public $carbon_footprint;            // Environmental impact
}
```

#### Production Time Calculation
```php
$timeToComplete = $quantity / $realSpeed;
$timeToComplete = max(1, $timeToComplete); // Minimum 1 day
```

#### Material Consumption
```php
foreach ($product->recipes as $recipe) {
    $requiredQuantity = $recipe->quantity × $productionQuantity;
    InventoryService::consumeMaterials($material, $requiredQuantity);
}
```

### 11. Sales Generation

#### Weekly Sales Process
1. **Demand Calculation**: Based on price, elasticity, and ads
2. **Client Distribution**: 1-3 clients per week
3. **Wilaya Assignment**: Random geographic distribution
4. **Quantity Allocation**: Weighted random distribution

#### Sales Distribution Algorithm
```php
// Generate random weights for sales
$weights = [];
for ($i = 0; $i < $numberOfSales; $i++) {
    $weight = rand(50, 150) / 100; // 0.5 to 1.5
    $weights[] = $weight;
    $totalWeight += $weight;
}

// Normalize to match total demand
foreach ($weights as $i => $weight) {
    $saleQuantities[$i] = ($weight / $totalWeight) × $totalDemand;
}
```

### 12. Procurement System

#### Supplier Management
```php
class Supplier {
    public $is_international;           // Local vs international
    public $min_shipping_cost;          // Shipping cost range
    public $max_shipping_cost;
    public $real_shipping_cost;         // Current cost
    public $carbon_footprint;           // Environmental impact
}
```

#### Purchase Process
1. **Supplier Selection**: Based on location and costs
2. **Shipping Calculation**: Cost + time + customs
3. **Delivery Tracking**: Time-based arrival
4. **Inventory Update**: Automatic stock addition

## Mathematical Models

### 1. PERT Distribution
Used for machine characteristics and maintenance:
```php
function calculatePertValue($min, $max) {
    $avg = ($min + $max) / 2;
    $expectedValue = ($min + 4 * $avg + $max) / 6;
    $standardDeviation = ($max - $min) / 6;
    
    // Normal distribution with randomness
    $u1 = rand(0, 100000) / 100000;
    $u2 = rand(0, 100000) / 100000;
    $z = sqrt(-2 * log($u1)) * cos(2 * pi() * $u2);
    
    $result = $expectedValue + ($z * $standardDeviation);
    return max($min, min($max, $result));
}
```

### 2. Demand Elasticity
```php
// Price elasticity of demand
$elasticity = $product->elasticity_coefficient;
$demand = $baseDemand - ($elasticity * $baseDemand * $priceRatio);

// Where priceRatio = (marketPrice - companyPrice) / marketPrice
```

### 3. Employee Mood System
```php
// Daily mood decay
$newMood = $currentMood - $decayRate;

// Salary impact on mood
$moodBoost = ($actualSalary - $minSalary) / ($maxSalary - $minSalary);
$newMood = min(1.0, $newMood + $moodBoost);
```

### 4. Machine Reliability
```php
// Daily reliability decay
$currentReliability = $initialReliability - ($days * $decayRate);

// Maintenance trigger
if ($currentReliability < $maintenanceThreshold) {
    triggerMaintenance();
}
```

## Database Schema

### Core Tables
- **companies**: Player companies with funds and research level
- **products**: Game products with recipes and technology requirements
- **technologies**: Research tree with costs and prerequisites
- **employees**: Company workers with profiles and mood
- **machines**: Production equipment with characteristics
- **suppliers**: Material providers with costs and locations
- **banks**: Financial institutions with loan terms
- **transactions**: Financial record keeping
- **inventory_movements**: Stock tracking and history

### Key Relationships
- **Company → Products**: Many-to-many through company_products
- **Company → Machines**: Many-to-many through company_machines
- **Company → Technologies**: Many-to-many through company_technologies
- **Product → Materials**: Many-to-many through product_recipes
- **Employee → Machine**: One-to-one assignment

## Game Mechanics

### Weekly Cycle
1. **Daily**: Production, maintenance, employee mood, and machine reliability
2. **Weekly (every 7 days)**: 
   - Inventory storage costs
   - Machine operation costs
   - Supplier price changes
   - Wilaya shipping cost updates
   - Sales generation
3. **Monthly (1st of month)**: 
   - Employee salary payments
   - Loan payments
4. **Continuous**: Technology research, purchase/sales processing, production orders

### Competition Elements
- **Market Share**: Based on pricing and quality
- **Technology Race**: R&D advancement speed
- **Efficiency**: Employee and machine optimization
- **Financial Management**: Cash flow and loan handling
- **Geographic Expansion**: Multi-wilaya operations

### Event System
- **Country Blockades**: Import restrictions
- **Custom Duty Changes**: Trade cost modifications
- **Inventory Damage**: Random loss events
- **Market Shocks**: Demand and price fluctuations

## Admin Features

### System Configuration
- **Game Settings**: Time progression and parameters
- **Product Management**: Demand curves and pricing
- **Technology Tree**: Research requirements and costs
- **Event Creation**: Dynamic game scenarios

### Monitoring Tools
- **Company Performance**: Financial and operational metrics
- **Market Analysis**: Demand and pricing trends
- **System Health**: Database and performance monitoring

## Company Features

### Business Operations
- **Production Planning**: Machine scheduling and optimization
- **Inventory Management**: Stock control and forecasting
- **Human Resources**: Employee hiring and retention
- **Financial Planning**: Cash flow and investment decisions

### Strategic Elements
- **Market Positioning**: Pricing and advertising strategies
- **Technology Investment**: R&D prioritization
- **Geographic Expansion**: Multi-location operations
- **Supplier Management**: Cost and reliability optimization

## Conclusion

This business simulation game provides a comprehensive platform for business education, combining multiple business disciplines into an engaging competitive environment. The system's mathematical models ensure realistic business dynamics while maintaining educational value for students learning about business operations, strategy, and management.

The modular architecture allows for easy expansion and customization, making it suitable for various educational contexts and business scenarios.
