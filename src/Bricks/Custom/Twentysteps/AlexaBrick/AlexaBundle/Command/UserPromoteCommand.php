<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Command;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations\Command;

use Bricks\AbstractCustomBundle\Command\AbstractUserPromoteCommand;

/**
 *
 * @Command(
 *     runOnce=true,
 *     defaultProjectCode="alexa",
 *     defaultBundleAlias="twentysteps_alexa"
 * )
 *
 */
class UserPromoteCommand extends AbstractUserPromoteCommand {}
