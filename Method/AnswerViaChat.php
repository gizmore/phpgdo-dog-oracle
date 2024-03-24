<?php
namespace GDO\DogOracle\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_UInt;
use GDO\DogOracle\GDT_OracleChoices;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_Validator;
use GDO\Form\MethodForm;
use GDO\Poll\GDO_Poll;
use GDO\Poll\GDO_PollAnswer;
use GDO\Poll\GDO_PollChoice;
use GDO\UI\GDT_Repeat;
use GDO\User\GDO_User;

/**
 * Answer via chat connectors.
 */
final class AnswerViaChat extends MethodForm
{

    public function isCLI(): bool
    {
        return true;
    }

    public function getCLITrigger(): string
    {
        return 'vote';
    }

    public function gdoParameters(): array
    {
        return [
            GDT_Object::make('id')->table(GDO_Poll::table())->notNull(),
        ];
    }

    /**
     * @throws GDO_ArgError
     */
    public function getPoll(): ?GDO_Poll
    {
        return $this->gdoParameterValue('id');
    }

    /**
     * @throws GDO_ArgError
     */
    protected function createForm(GDT_Form $form): void
    {
        $form->addFields(
            GDT_Object::make('id')->table(GDO_Poll::table())->notNull(),
            GDT_Repeat::makeAs('answers', GDT_UInt::make()),
        );
        $form->addFields(
            GDT_Validator::make()->validatorFor($form, 'id', [$this, 'validateAnswer']),
        );

        $form->actions()->addField(GDT_Submit::make());
    }

    /**
     * @throws GDO_ArgError
     */
    public function validateAnswer(GDT_Form $form, GDT $field, ?GDO_Poll $poll): bool
    {
        $choices = $poll->getChoicesSorted();
        $chosen = array_unique($form->getFormVar('answers'));
        if (count($chosen) > $poll->getMaxAnswers())
        {
            return $field->error('err_dog_oracle_answer_count', [$poll->getMaxAnswers()]);
        }
        foreach ($chosen as $c)
        {
            if (($c < 1) || ($c > count($choices)))
            {
                return $field->error('err_dog_oracle_answer_num', [1, count($choices)]);
            }
        }
        return true;
    }

    /**
     * @throws GDO_ArgError
     */
    public function formValidated(GDT_Form $form): GDT
    {
        $user = GDO_User::current();
        $poll = $this->getPoll();
        $choices = array_values($poll->getChoicesSorted());
        $answers = array_unique($form->getFormVar('answers'));

        GDO_PollAnswer::clearPollFor($user, $poll);

        foreach ($answers as $c)
        {
            $choice = $choices[$c-1];
            GDO_PollAnswer::blank([
                'answer_user' => $user->getID(),
                'answer_choice' => $choice->getID(),
            ])->insert();
        }

        $poll->recalculate();

//        if ($isUpdate)
//        {
//            GDT_Hook::callWithIPC('PollVoteUpdated', $user, $poll, $answers);
//        }
//        else
//        {
//            GDT_Hook::callWithIPC('PollVoteCreated', $user, $poll, $answers);
//        }

        return $this->message('msg_dog_voted');
    }

}
