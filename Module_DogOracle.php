<?php
namespace GDO\DogOracle;

use GDO\Core\Application;
use GDO\Core\GDO;
use GDO\Core\GDO_Module;
use GDO\Dog\Dog;
use GDO\Dog\DOG_Server;
use GDO\Mail\GDT_MailAuth;
use GDO\Mail\Mail;
use GDO\Poll\GDO_Poll;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;
use GDO\Util\Arrays;

/**
 * Dog adapter for module Poll.
 * Handles announcements for subscriptions
 */
final class Module_DogOracle extends GDO_Module
{

    public function getDependencies(): array
    {
        return [
            'DogAuth',
            'Poll',
            'Subscription',
        ];
    }

    public function getFriendencies(): array
    {
        return [
            'Mail',
            'DogIRC',
            'DogTelegram',
        ];
    }

    public function getClasses(): array
    {
        return [
            DOG_OracleAsk::class,
        ];
    }

    public function onLoadLanguage(): void
    {
        $this->loadLanguage('lang/oracle');
    }

    public function clihookPollAdded(string $pollid): void
    {
         $poll = GDO_Poll::getById($pollid);
         (new Announcer($poll))->doIt();
    }

}
