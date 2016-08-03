<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Core\View;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Breadcrumbs are used in conjunction with the content header to inform users of what they're doing - and where they're
 * doing it.
 *
 * @see https://mot-styleguide.herokuapp.com/molecule/breadcrumbs/
 */
abstract class Breadcrumbs extends ArrayCollection
{

}