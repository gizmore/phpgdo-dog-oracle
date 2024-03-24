<?php
return [

    'mt_dogoracle_newpoll' => 'Create a new poll',
    'msg_poll_created' => 'Your poll has been created and broadcasted.',
//    'dog_poll_created' => 'A new poll has been created: %s. Vote with vote %s %s.',

    'dog_poll_created' => 'New Poll (ID: %s): %s - vote with %s%s %1$s, %s.',

    'mails_poll_created' => 'New Poll',
    'mailb_poll_created' => '
    Dear %s,
    
    There has been a new poll created on %s.
    
    %s
    
    Possible answers (%s can be selected):
    
    %s
    
    You can vote via this link: %s
    
    Kind Regards
    The %2$s system',

    # Answer
    'err_dog_oracle_answer_count' => 'You may answer %s different choices.',
    'err_dog_oracle_answer_num' => 'You can only choose values between %s and %s.',
    'msg_dog_voted' => 'Your vote has been recorded.',

    # Newpoll
    'msg_dog_poll_created' => 'Your poll has been created and broadcasted.',
    'msg_dog_poll_preview' => 'Your poll would be: %s - Answers: %s (NumChoices: %s). Use the --save switch to create it.',
];
