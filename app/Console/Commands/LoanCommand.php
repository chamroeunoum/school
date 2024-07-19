<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LoanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:interest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate interest transaction for a loan.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = \Carbon\Carbon::now()->format('Y-m-d');
        /** Check whether the interest transaction of the current date does exist */
        if ( $counter = \App\Models\LoanTransaction::where('repayment_date', "LIKE", $today."%")->count() ) {
            echo "\n====================\n= LOAN INTEREST CALCULATION\n====================\nThe calculation of loan interest has executed before already.\nThere are ".$counter." records executed.\n====================\n" ;
        }else{
            /** Check whether today is the meeting date, if so the calculation must be executed */
            $loans = \App\Models\Loan::get()->map(function ($loan) {
                $loan->calculateInterest();
                return $loan ;
            });
            echo "\n====================\n= LOAN INTEREST CALCULATION\n====================\nThe calculation of loan interest has executed for ".count( $loans )." records.\n====================\n" ;
        }
    }
}
