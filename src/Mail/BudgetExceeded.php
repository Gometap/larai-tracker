<?php

namespace Gometap\LaraiTracker\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BudgetExceeded extends Mailable
{
    use Queueable, SerializesModels;

    public $budget;
    public $currentCost;
    public $currencySymbol;

    public function __construct($budget, $currentCost, $currencySymbol = '$')
    {
        $this->budget = $budget;
        $this->currentCost = $currentCost;
        $this->currencySymbol = $currencySymbol;
    }

    public function build()
    {
        return $this->subject('⚠️ [Larai Tracker] AI Budget Alert')
            ->html("
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                    <h2 style='color: #ef4444;'>Budget Threshold Exceeded</h2>
                    <p>Hello,</p>
                    <p>This is an automated alert from <strong>Larai Tracker</strong>.</p>
                    <p>Your AI spending has reached the defined alert threshold:</p>
                    <ul style='list-style: none; padding: 0;'>
                        <li><strong>Planned Budget:</strong> {$this->currencySymbol}{$this->budget->amount}</li>
                        <li><strong>Alert Threshold:</strong> {$this->budget->alert_threshold}%</li>
                        <li><strong>Current Spending (This Month):</strong> <span style='color: #ef4444; font-weight: bold;'>{$this->currencySymbol}{$this->currentCost}</span></li>
                    </ul>
                    <p style='margin-top: 20px;'>Please check your <a href='" . route('larai.dashboard') . "'>Larai Dashboard</a> for more details.</p>
                    <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #64748b;'>&copy; " . date('Y') . " Gometap Group - Larai Tracker</p>
                </div>
            ");
    }
}
