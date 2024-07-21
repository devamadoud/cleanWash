<?php
namespace App\Services;

use App\Entity\Collecte;
use App\Entity\Invoice;
use App\Entity\Order;
use DateTimeImmutable;

class InvoiceService
{
    public function createInvoice(object $data) : Invoice
    {
        if($data instanceof Collecte){
            $invoice = new Invoice();
            $invoice->setCreatedAt(new DateTimeImmutable())
                ->setType('collecte')
                ->setCollecte($data)
                ->setShop($data->getCustomer()->getShop())
            ;
            return $invoice;
        }

        if($data instanceof Order){

            $invoice = new Invoice();
            $invoice->setCreatedAt(new DateTimeImmutable())
                ->setType('order')
                ->setOrderInvoice($data)
                ->setShop($data->getCustomer()->getShop())
            ;
            return $invoice;
        }
    }

}