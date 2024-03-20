<?php
namespace GDO\DogOracle\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Core\GDT_DeletedAt;
use GDO\Core\GDT_DeletedBy;
use GDO\Core\GDT_String;
use GDO\Core\GDT_UInt;
use GDO\Date\GDT_Date;
use GDO\DogOracle\Module_DogOracle;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Language\GDT_Language;
use GDO\Poll\GDO_Poll;
use GDO\Poll\GDO_PollAnswer;
use GDO\Poll\GDO_PollChoice;
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Repeat;
use GDO\UI\GDT_Title;
use GDO\User\GDO_User;
use GDO\User\GDT_Level;

final class NewPoll extends MethodForm
{

    public function isCLI(): bool
    {
        return true;
    }

    public function getCLITrigger(): string
    {
        return 'poll.new';
    }

    protected function createForm(GDT_Form $form): void
    {
        $polls = GDO_Poll::table();
        $user = GDO_User::current();
        $form->addFields(
//            $polls->gdoColumn('poll_guests'),
//            $polls->gdoColumn('poll_expires'),
//            $polls->gdoColumn('poll_language'),
//            $polls->gdoColumn('poll_max_answers'),
            GDT_String::make('question')->notNull(),
//            $polls->gdoColumn('poll_description'),
            GDT_Repeat::makeAs('answers', GDT_String::make()->max(128)),
            GDT_AntiCSRF::make(),
        );
        $form->actions()->addField(GDT_Submit::make());
    }

    public function formValidated(GDT_Form $form): GDT
    {
        $vars = $form->getFormVars();
        $poll = GDO_Poll::blank([
            'poll_question' => $vars['question'],
        ])->insert();
        foreach ($vars['answers'] as $answer)
        {
            GDO_PollChoice::blank([
                'choice_poll' => $poll->getID(),
                'choice_text' => $answer,
            ])->insert();
        }

        Module_DogOracle::instance()->hookPollAdded($poll->getID());

        return $this->message('msg_poll_created');
    }

}
