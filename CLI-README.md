# Command line interface (CLI)

You will need to edit a Doctrine file for this to work.
Goto
Doctrine/ORM/Tools/Console/ConsoleRunner.php

Add this

    // Fixtures
    new \Doctrine\Common\DataFixtures\Command\Add(),

after

    new \Doctrine\ORM\Tools\Console\Command\InfoCommand(),

Now you can use
    
    ./doctrine fixtures:add

## Options to fixtures:add
    
    --directory (-d) Directory with your fixtures - Its relative to your Entities\Proxies path *REQUIRED*
    --append (-a) If you want to append your fixtures instead
    --dump-fixtures Vieweing the fixtures instead of importing them

