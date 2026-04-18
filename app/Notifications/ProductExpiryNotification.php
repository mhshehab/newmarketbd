<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductExpiryNotification extends Notification
{
    use Queueable;

    public $product;

    /**
     * Create a new notification instance.
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Product Expiry Warning',
            'body' => "Product: {$this->product->name} (Expiry: {$this->product->expiry_date->format('d/m/Y')})",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'expiry_date' => $this->product->expiry_date->format('d/m/Y'),
            'type' => 'warning',
            'icon' => 'heroicon-o-calendar',
            'actions' => [
                [
                    'label' => 'View Product',
                    'url' => '/admin/products/' . $this->product->id . '/edit'
                ]
            ]
        ];
    }
}
