<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// প্রতিদিন রাত ১২টায় আপনার এক্সপায়ারি চেক কমান্ডটি রান করবে
Schedule::command('app:check-product-expiry')->daily();

// আপনি যদি টেস্ট করার জন্য প্রতি মিনিটে চেক করতে চান (শুধুমাত্র চেকিংয়ের জন্য):
// Schedule::command('app:check-product-expiry')->everyMinute();
