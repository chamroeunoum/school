<?php

namespace App\Notifications\Webapp;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SignupActivate extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        /**
         * Use the link to activate the account
         */
        // $url = url('/api/auth/signup/activate/'.$notifiable->activation_token);
        // return (new MailMessage)
        //     ->subject('Confirm your account')
        //     ->line('Thanks for signup! Please before you begin, you must confirm your account.')
        //     ->action('Confirm Account', url($url))
        //     ->line('Thank you for using our application!');
        /**
         * Use the 6 digits code to activate account
         */
        return (new MailMessage)
        ->subject('បញ្ជាក់អំពីការចុះឈ្មោះ - ឯកសារអេឡិចត្រូនិច')
        ->greeting("សួស្ដី, អ៊ុំ ចំរើន")
        ->line("ខាងក្រោមជា លេខកូត ដែលអ្នកនិងត្រូវការដើម្បីបញ្ជាក់អំពីការចុះឈ្មោះរបស់អ្នកជាមួយ អសយដ្ឋានអេឡិចត្រូនិច មួយនេះ។")
        ->line( $notifiable->activation_token )
        ->line("សូមបញ្ចូលលេខកូតនេះទៅក្នុងទំព័រ បញ្ជាក់គណនីរបស់ប្រព័ន្ធឯកសារអេឡិចត្រូនិច ដើម្បីបញ្ចប់តំណើរការចុះឈ្មោះរបស់អ្នកជាស្ថាពរ។")
        ->line("ប្រព័ន្ធឯកសារអេឡិចត្រូនិច - ទីស្ដីការគណរដ្ឋមន្ត្រី - អគ្គនាយកដ្ឋានសម្របសម្រួលកិច្ចការទូទៅ - នាយកដ្ឋានឯកសារអេឡិចត្រូនិច និងព័ត៌មានវិទ្យា");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
