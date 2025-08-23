# Business Game - Complete Game Mechanics & Calculations

## 🎮 Game Overview

The Business Game is a comprehensive business simulation where players manage virtual companies through various economic cycles, production processes, and strategic decisions. This document provides detailed explanations of all game mechanics, calculations, and systems.

## 🏢 Company Management System

### Company Creation & Initial Setup

-   **Starting Capital**: Each company begins with a configurable starting amount (default: $100,000)
-   **Initial Assets**: Basic office space, minimal equipment, and starting inventory
-   **Company Rating**: Starts at 1.0 and improves based on performance metrics

### Company Rating Calculation

```
Company Rating = Base Rating + Performance Bonus + Market Position + Technology Level

Base Rating: 1.0
Performance Bonus: (Profit Growth + Market Share + Employee Satisfaction) / 3
Market Position: (Revenue Rank / Total Companies) * 2
Technology Level: (Total Technologies / Max Technologies) * 1.5

Final Rating: Capped between 0.1 and 10.0
```

## 👥 Employee Management System

### Employee Recruitment

-   **Recruitment Cost**: Base cost + (Skill Level × Multiplier)
-   **Base Cost**: $5,000
-   **Skill Multiplier**: 1.5x per skill level
-   **Maximum Skills**: 5 per employee

### Employee Salary Calculation

```
Monthly Salary = Base Salary + (Skill Level × Skill Bonus) + Experience Bonus + Performance Bonus

Base Salary: $3,000
Skill Bonus: $500 per skill level
Experience Bonus: (Years Employed × $200)
Performance Bonus: (Productivity Score × $100)
```

### Employee Productivity

```
Productivity = (Skill Level × 0.3) + (Happiness × 0.4) + (Training × 0.3)

Skill Level: 1-5 (affects production quality)
Happiness: 0-100% (based on salary, work conditions, promotions)
Training: 0-100% (increases with training programs)
```

### Employee Promotion System

-   **Promotion Requirements**:
    -   Minimum 6 months employment
    -   Productivity score > 80%
    -   Available management positions
-   **Promotion Benefits**:
    -   15% salary increase
    -   10% productivity boost
    -   Management bonuses

## 🏭 Production System

### Machine Management

-   **Machine Purchase Cost**: Base cost + (Efficiency × Multiplier)
-   **Maintenance Cost**: 2% of machine value per month
-   **Efficiency Decay**: 1% per month without maintenance
-   **Maximum Efficiency**: 100%

### Production Order Calculation

```
Production Cost = Raw Materials + Labor + Machine Depreciation + Overhead

Raw Materials: Sum of all required materials × current market prices
Labor: (Production Time × Employee Count × Average Hourly Rate)
Machine Depreciation: (Machine Value × 0.02) / Production Cycles
Overhead: Fixed monthly costs / Production Volume
```

### Production Quality

```
Quality Score = (Machine Efficiency × 0.4) + (Employee Skills × 0.3) + (Material Quality × 0.2) + (Process Control × 0.1)

Quality Multipliers:
- Excellent (90-100%): 1.2x price
- Good (70-89%): 1.0x price
- Average (50-69%): 0.8x price
- Poor (30-49%): 0.6x price
- Very Poor (0-29%): 0.4x price
```

### Production Time Calculation

```
Production Time = Base Time × Complexity × Efficiency Factor

Base Time: Standard time for product type
Complexity: Product difficulty rating (1-10)
Efficiency Factor: (Machine Efficiency + Employee Skills) / 2
```

## 💰 Financial System

### Revenue Calculation

```
Revenue = (Quantity Sold × Unit Price × Quality Multiplier) + Bulk Discounts + Market Bonuses

Unit Price: Base cost + markup percentage
Quality Multiplier: Based on production quality score
Bulk Discounts: 5% for orders > 100 units, 10% for > 500 units
Market Bonuses: Seasonal demand, market shortages, etc.
```

### Cost Structure

```
Total Cost = Fixed Costs + Variable Costs + Interest + Taxes

Fixed Costs: Rent, utilities, insurance, management salaries
Variable Costs: Materials, labor, shipping, commissions
Interest: Loan payments, credit lines
Taxes: Corporate tax rate × (Revenue - Deductions)
```

### Profit Margin Calculation

```
Gross Profit = Revenue - Cost of Goods Sold
Net Profit = Gross Profit - Operating Expenses - Interest - Taxes
Profit Margin = (Net Profit / Revenue) × 100

Target Profit Margins:
- Manufacturing: 15-25%
- Services: 20-35%
- Retail: 10-20%
```

### Loan System

-   **Interest Rate**: Base rate + (Company Rating × Risk Factor)
-   **Base Rate**: 5% annually
-   **Risk Factor**: 0.5% per rating point below 5.0
-   **Maximum Loan**: 3x monthly revenue
-   **Repayment Period**: 12-60 months

## 📊 Market Dynamics

### Supply & Demand

```
Demand = Base Demand × Seasonal Factor × Market Growth × Competition Factor
Supply = Total Industry Production × Capacity Utilization × Quality Factor

Price Impact = (Demand - Supply) / Supply × Price Sensitivity
Price Sensitivity: 0.1-0.3 depending on product type
```

### Market Share Calculation

```
Market Share = Company Sales / Total Market Sales × 100

Market Share Bonuses:
- >50%: 15% price premium
- 25-50%: 10% price premium
- 10-25%: 5% price premium
- <10%: No premium
```

### Competition Analysis

```
Competitive Position = (Company Rating / Average Industry Rating) × Market Share

Competitive Advantages:
- Higher rating: Better pricing power
- Larger market share: Economies of scale
- Technology lead: Innovation premium
```

## 🛒 Inventory Management

### Stock Level Optimization

```
Optimal Stock = (Average Monthly Demand × Lead Time) + Safety Stock

Safety Stock = (Maximum Demand - Average Demand) × Safety Factor
Safety Factor: 1.5 for critical items, 1.2 for standard items
```

### Inventory Costs

```
Holding Cost = Average Inventory × Unit Cost × Monthly Holding Rate
Holding Rate: 2% per month (storage, insurance, obsolescence)

Stockout Cost = Lost Sales × Profit Margin × Stockout Frequency
```

### Reorder Point

```
Reorder Point = (Daily Demand × Lead Time) + Safety Stock

Lead Time: Supplier delivery time + processing time
```

## 🌍 Geographic Expansion

### Wilaya (Region) System

-   **Transportation Costs**: Base cost + (Distance × Rate per km)
-   **Market Access**: Population × Income Level × Market Penetration
-   **Competition Level**: Number of competitors × Their market share

### Expansion Benefits

```
Market Access Bonus = (New Population / Total Population) × Income Multiplier
Income Multiplier: Based on regional economic development
Transportation Discount: 10% for bulk shipments, 20% for regular routes
```

## 🔬 Technology & Innovation

### Research & Development

-   **R&D Cost**: Base cost × Technology complexity
-   **Success Rate**: Base 60% + (R&D Team Skills × 0.4)
-   **Development Time**: 3-12 months depending on complexity

### Technology Benefits

```
Efficiency Improvement = Technology Level × 0.15
Cost Reduction = Technology Level × 0.10
Quality Enhancement = Technology Level × 0.20
```

### Technology Adoption

```
Adoption Rate = (Company Rating × 0.3) + (Market Position × 0.4) + (Financial Health × 0.3)
Adoption Cost: Technology Cost × (1 - Adoption Rate)
```

## 📈 Performance Metrics

### Key Performance Indicators (KPIs)

1. **Revenue Growth**: Month-over-month revenue increase
2. **Profit Margin**: Net profit as percentage of revenue
3. **Market Share**: Company's portion of total market
4. **Employee Satisfaction**: Average happiness score
5. **Asset Utilization**: Production capacity usage
6. **Return on Investment**: Net profit / total assets

### Performance Scoring

```
Overall Score = (Revenue Growth × 0.25) + (Profit Margin × 0.25) + (Market Share × 0.20) + (Employee Satisfaction × 0.15) + (Asset Utilization × 0.15)

Score Ranges:
- 90-100: Excellent
- 80-89: Good
- 70-79: Average
- 60-69: Below Average
- <60: Poor
```

## 🎯 Game Events & Scenarios

### Economic Events

-   **Recession**: 20% demand reduction, 15% cost increase
-   **Boom**: 25% demand increase, 10% cost reduction
-   **Inflation**: 5-15% cost increase across all categories
-   **Currency Fluctuation**: Import/export cost variations

### Market Events

-   **Supply Shortages**: 30-50% price increases
-   **New Competitors**: Market share redistribution
-   **Regulatory Changes**: Compliance costs and restrictions
-   **Technology Disruption**: New products entering market

### Company Events

-   **Employee Strikes**: 50% productivity reduction
-   **Equipment Failures**: Production delays and repair costs
-   **Legal Issues**: Fines and reputation damage
-   **Natural Disasters**: Facility damage and insurance claims

## 🔧 Admin Configuration Variables

### Economic Parameters

```php
// Base interest rates
'base_interest_rate' => 0.05, // 5%
'risk_factor_multiplier' => 0.005, // 0.5% per rating point

// Tax rates
'corporate_tax_rate' => 0.25, // 25%
'vat_rate' => 0.20, // 20%

// Inflation settings
'monthly_inflation_rate' => 0.01, // 1% per month
'inflation_volatility' => 0.005, // ±0.5% variation
```

### Production Parameters

```php
// Quality multipliers
'quality_multipliers' => [
    'excellent' => 1.2, // 90-100%
    'good' => 1.0,      // 70-89%
    'average' => 0.8,   // 50-69%
    'poor' => 0.6,      // 30-49%
    'very_poor' => 0.4  // 0-29%
],

// Efficiency decay
'monthly_efficiency_decay' => 0.01, // 1% per month
'maintenance_cost_rate' => 0.02,    // 2% of machine value
```

### Market Parameters

```php
// Demand factors
'seasonal_demand_variation' => 0.3, // ±30% seasonal change
'market_growth_rate' => 0.02,       // 2% monthly growth
'price_sensitivity_range' => [0.1, 0.3],

// Competition factors
'market_share_bonuses' => [
    50 => 0.15, // >50% = 15% premium
    25 => 0.10, // 25-50% = 10% premium
    10 => 0.05  // 10-25% = 5% premium
]
```

### Employee Parameters

```php
// Salary structure
'base_salary' => 3000,
'skill_bonus_per_level' => 500,
'experience_bonus_per_year' => 200,
'productivity_bonus_multiplier' => 100,

// Productivity factors
'skill_weight' => 0.3,
'happiness_weight' => 0.4,
'training_weight' => 0.3
```

## 📊 Database Calculations

### Real-time Updates

The game performs calculations in real-time for:

-   **Daily**: Employee productivity, machine efficiency
-   **Weekly**: Market demand, competitor analysis
-   **Monthly**: Financial statements, performance metrics
-   **Quarterly**: Tax calculations, major events

### Performance Optimization

-   **Caching**: Frequently accessed calculations stored in cache
-   **Batch Processing**: Non-critical updates processed in background
-   **Database Indexing**: Optimized queries for large datasets
-   **Memory Management**: Efficient data structures for calculations

## 🎮 Player Strategy Guide

### Starting Strategies

1. **Conservative**: Focus on stable products, minimal debt
2. **Aggressive**: High investment in R&D, rapid expansion
3. **Balanced**: Moderate growth with risk management

### Growth Tactics

-   **Market Penetration**: Lower prices to gain market share
-   **Product Development**: Invest in R&D for competitive advantage
-   **Market Expansion**: Enter new regions and product categories
-   **Acquisition**: Purchase competitors or complementary businesses

### Risk Management

-   **Diversification**: Multiple product lines and markets
-   **Financial Reserves**: Maintain cash for emergencies
-   **Insurance**: Protect against major losses
-   **Hedging**: Offset currency and commodity risks

## 🔍 Debugging & Testing

### Admin Tools

-   **Variable Inspector**: View all current game parameters
-   **Calculation Tester**: Test specific formulas with sample data
-   **Performance Monitor**: Track system performance and bottlenecks
-   **Data Validator**: Check for data integrity issues

### Testing Scenarios

-   **Stress Testing**: High player count and complex calculations
-   **Edge Cases**: Boundary conditions and extreme values
-   **Regression Testing**: Ensure changes don't break existing functionality
-   **Load Testing**: Performance under normal and peak conditions

---

**Note**: This document is updated with each game version. For the latest calculations and mechanics, refer to the current game code and admin panel.
