<?php

arch()->preset()->php();

arch()
    ->expect('Yilanboy\Preview')
    ->toUseStrictTypes();
