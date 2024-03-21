<?php
namespace GDO\DogOracle;

use GDO\Core\GDO_DBException;
use GDO\Core\Logger;
use GDO\Dog\Dog;
use GDO\Dog\DOG_Server;
use GDO\Dog\DOG_User;
use GDO\DogOracle\Method\AnswerViaChat;
use GDO\Mail\GDT_MailAuth;
use GDO\Mail\Mail;
use GDO\Poll\GDO_Poll;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;

final class Announcer
{

    private GDO_Poll $poll;

    private string $choicesMail;

    private string $choicesChat;

    public function __construct(GDO_Poll $poll)
    {
        $this->poll = $poll;
    }

    public function doIt(): void
    {
        $this->announcePoll($this->poll);
    }

    private function announcePoll(GDO_Poll $poll): void
    {
        $this->choicesChat = $this->renderPollChoicesForChat($poll);
        $this->choicesMail = $this->renderPollChoicesForMail($poll);

        $this->announcePollToServers($poll);

        $result = GDO_User::withSettingResult('Poll', 'poll_subscription', null, 'IS NOT');
        while ($user = $result->fetchObject())
        {
            $this->announcePollToUser($user, $poll);
        }
    }

    private function renderPollChoicesForMail(GDO_Poll $poll): string
    {
        return GDT_OracleChoices::make()->poll($poll)->renderChoicesForMail();
    }

    private function renderPollChoicesForChat(GDO_Poll $poll): string
    {
        return GDT_OracleChoices::make()->poll($poll)->renderChoicesForChat();
    }

    private function announcePollToServers(GDO_Poll $poll): void
    {
        foreach (Dog::instance()->servers as $server)
        {
            $this->announcePollToServer($poll, $server);
        }
   }

    /**
     * @param GDO_Poll $poll
     * @param DOG_Server $server
     * @return void
     */
    private function announcePollToServer(GDO_Poll $poll, DOG_Server $server): void
    {
        foreach ($server->rooms as $room)
        {
            $room->send($this->renderChatMessage($poll, $room->getTrigger(), $room->getLanguageISO()));
        }
    }

    private function renderChatMessage(GDO_Poll $poll, string $trigger, string $iso): string
    {
        $method = AnswerViaChat::make();
        $command = $method->getCLITrigger();
        $args = [
            $poll->getID(),
            $poll->renderTitle(),
            $trigger,
            $command,
            $this->choicesChat,
        ];
        return tiso($iso, 'dog_poll_created', $args);

    }

    private function announcePollToUser(GDO_User $user, GDO_Poll $poll): void
    {
        $subscriptions = $user->settingValue('Poll', 'poll_subscription');
        foreach ($subscriptions as $moduleName)
        {
            $methodName = "announcePollToUserVia{$moduleName}";
            if (method_exists($this, $methodName))
            {
                call_user_func([$this, $methodName], $user, $poll);
            }
            else
            {
                Logger::logError("Unknown announce subscriptor: $methodName");
            }
        }
    }

    public function announcePollToUserViaMail(GDO_User $user, GDO_Poll $poll): void
    {
        if ($user->hasMail())
        {
            $token = GDT_MailAuth::generateToken($user->getID());
            $params = "&mxauth={$token}&id={$poll->getID()}";
            $link = GDT_Link::make()->href(url('DogOracle', 'AnswerViaMail', $params))->render();
            $mail = Mail::botMail()->lazy();
            $mail->setSubject(tusr($user, 'mails_poll_created', [$poll->renderTitle()]));
            $args = [
                $user->renderUserName(),
                sitename(),
                $poll->renderTitle(),
                $poll->getMaxAnswers(),
                $this->choicesMail,
                $link,
            ];
            $mail->setBody(tusr($user, 'mailb_poll_created', $args));
            $mail->sendToUser($user);
        }
    }


    /**
     * @throws GDO_DBException
     */
    public function announcePollToUserViaDogTelegram(GDO_User $user, GDO_Poll $poll): void
    {
        $server = DOG_Server::getByConnector('Telegram');
        if ($u = DOG_User::getFor($user))
        {
            $u->send($this->renderChatMessage($poll, $server->getConnector()->getTrigger(), $user->getLangISO()));
        }
    }

    public function announcePollToUserViaDogIRC(GDO_User $user, GDO_Poll $poll): void
    {
//        if ($u = DOG_User::getFor($user))
//        {
//
//        }
//        if ($user DOG_User::getFor($user)
//        {
//
//        }

    }

}
