<?php

namespace App\Logger;

// Неправильно (вызывает ошибку):
// use App\Logger\EntityManagerInterface;

// Правильно:
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DbAuditHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, \Monolog\Level $level = \Monolog\Level::Info, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->em = $em;
    }

    protected function write(LogRecord $record): void
    {
        $audit = new \App\Entity\AuditLog();
        $audit->setUsername($record->context['username']);
        $audit->setAction($record->message);
        $audit->setEntity($record->context['entity'] ?? '');
        $audit->setEntityId($record->context['entityId'] ?? 0);
        $audit->setDif($record->context['diff'] ?? '');
        $audit->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($audit);
        $this->em->flush();
    }
}
