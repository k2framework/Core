<?php

namespace KumbiaPHP\Kernel\Event;

/**
 * Description of KumbiaEvents
 *
 * @author manuel
 */
final class KumbiaEvents
{

    const REQUEST = 'kumbia.request';
    const CONTROLLER = 'kumbia.controller';
    const RESPONSE = 'kumbia.response';
    const TERMINATE = 'kumbia.terminate';
    const EXCEPTION = 'kumbia.exception';

}