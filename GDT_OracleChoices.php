<?php
namespace GDO\DogOracle;

use GDO\Core\GDT_Select;
use GDO\Poll\GDO_Poll;

final class GDT_OracleChoices extends GDT_Select
{

    protected function __construct()
    {
        parent::__construct();
    }

    public GDO_Poll $poll;

    public function poll(GDO_Poll $poll): self
    {
        $this->poll = $poll;
        return $this;
    }

    public function getChoices(): array
    {
        $n = 1;
        $choices = [];
        foreach ($this->poll->getChoicesSorted() as $choice)
        {
            $choices[(string)($n++)] = $choice->renderTitle();
        }
        return $choices;
    }

    public function renderChoicesForMail(): string
    {
        $choices = [];
        foreach ($this->poll->getChoicesSorted() as $choice)
        {
            $choices[] = $choice->renderTitle();
        }
        return implode("\n", $choices);
    }

    public function renderChoicesForChat(): string
    {
        $n = 1;
        $choices = [];
        foreach ($this->poll->getChoicesSorted() as $choice)
        {
            $choices[] = "{$n}-{$choice->renderTitle()}";
            $n++;
        }
        $op = $this->poll->isMultipleChoice() ? '&' : ',';
        return implode($op, $choices);
    }


}
