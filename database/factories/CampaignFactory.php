<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $status = $this->faker->randomElement(['active', 'paused', 'completed', 'draft']);
        $type = $this->faker->randomElement(['Email', 'SMS', 'Social', 'Search', 'Display']);
        
        $budget = $this->faker->randomFloat(2, 500, 50000);
        
        // Logical metrics based on status
        if ($status === 'draft') {
            $spent = 0;
            $impressions = 0;
            $clicks = 0;
            $conversions = 0;
        } else {
            // Active, paused, or completed campaigns have spent some budget
            $spentPercent = $status === 'active' 
                ? $this->faker->randomFloat(2, 5, 85) 
                : $this->faker->randomFloat(2, 80, 100);
            
            $spent = ($budget * $spentPercent) / 100;
            
            // Set performance metrics
            $impressions = $this->faker->numberBetween(10000, 500000);
            // Average CTR is 1% to 8%
            $ctr = $this->faker->randomFloat(4, 0.01, 0.08);
            $clicks = (int) ($impressions * $ctr);
            
            // Average conversion rate is 2% to 15% of clicks
            $conversionRate = $this->faker->randomFloat(4, 0.02, 0.15);
            $conversions = (int) ($clicks * $conversionRate);
        }

        $startDate = $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d');
        // Draft campaigns may not have end dates yet, ongoing campaigns may have null end date
        $endDate = null;
        if ($status === 'completed') {
            $endDate = $this->faker->dateTimeBetween($startDate, 'now')->format('Y-m-d');
        } elseif ($status !== 'draft' && $this->faker->boolean(70)) {
            $endDate = $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d');
        }

        return [
            'name' => $this->faker->randomElement([
                'Summer Collection Launch',
                'Q3 Email Retargeting',
                'Cart Abandonment Recovery',
                'Google Search: Brand Keywords',
                'Facebook Lookalike Prospecting',
                'SMS Flash Sale Alert',
                'Display: High-Intent Users',
                'Influencer Partnership Campaign',
                'Customer Winback Email Series',
                'Holiday Gift Guide Promo',
                'Black Friday Warm-up',
                'New Product Announcement',
                'Newsletter Growth Push',
                'YouTube Video Pre-roll Ads',
                'Local Search SEO Campaign',
                'Spring Clearance Event',
                'Loyalty Program Launch',
                'Referral Program Push',
                'B2B LinkedIn Outreach',
                'Podcast Sponsorship Series'
            ]) . ' (' . $type . ')',
            'description' => $this->faker->sentence(12),
            'status' => $status,
            'type' => $type,
            'budget' => $budget,
            'spent' => $spent,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => $conversions,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
