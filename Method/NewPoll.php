<?php
namespace GDO\DogOracle\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDO_DBException;
use GDO\Core\GDO_Exception;
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
use GDO\Form\GDT_Validator;
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
use GDO\Util\Arrays;

final class NewPoll extends MethodForm
{

    public function isCLI(): bool
    {
        return true;
    }

    public function getCLITrigger(): string
    {
        return 'newpoll';
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
            GDT_Checkbox::make('save')->notNull()->initial('1'),
            GDT_UInt::make('multiple')->min(1),
            GDT_String::make('question')->notNull(),
//            $polls->gdoColumn('poll_description'),
            GDT_Repeat::makeAs('answers', GDT_String::make()->max(128)->label('answers')->notNull()),
        );
        $form->addFields(
            GDT_Validator::make()->validatorFor($form, 'multiple', [$this, 'validateMultiple']),
            GDT_AntiCSRF::make(),
        );
        $form->actions()->addField(GDT_Submit::make());
    }

    /**
     * @throws GDO_DBException
     * @throws GDO_Exception
     */
    public function validateMultiple(GDT_Form $form, GDT_UInt $field, $value): bool
    {
        $answers = $form->getFormVar('answers');
        $field->max(count($answers));
        return $field->validate($field->getValue());
    }

    /**
     * @throws GDO_ArgError
     */
    public function wantSave(): bool
    {
        return $this->gdoParameterValue('save');
    }

    /**
     * @throws GDO_ArgError
     */
    public function formValidated(GDT_Form $form): GDT
    {
        $co = [];
        $save = $this->wantSave();
        $vars = $form->getFormVars();
        $poll = GDO_Poll::blank([
            'poll_question' => $vars['question'],
            'poll_max_answers' => $vars['multiple'],
        ]);
        if ($save)
        {
            $poll->insert();
        }
        $choices = [];
        $n = 1;
        foreach ($vars['answers'] as $answer)
        {
            $choice = GDO_PollChoice::blank([
                'choice_poll' => $poll->getID(),
                'choice_text' => $answer,
            ]);
            if ($save)
            {
                $choice->insert();
            }
            $co[] = "{$n}-{$answer}";
            $n++;
        }

        if ($save)
        {
            Module_DogOracle::instance()->clihookPollAdded($poll->getID());
            return $this->message('msg_dog_poll_created');
        }
        else
        {
            #     'msg_dog_poll_preview' => 'Your poll would be: %s - Answers: %s (NumChoices: %s). Use the --save switch to create it.',
            return $this->message('msg_dog_poll_preview', [$poll->renderTitle(), Arrays::implodeHuman($co), $vars['multiple']]);
        }

    }

}
