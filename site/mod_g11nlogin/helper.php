<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') || die('=;)');

/**
 * Enter description here ...
 *
 * @author elkuku
 *
 */
class modg11nLoginHelper
{
    public static function getReturnURL($params, $type)
    {
        $url = null;

        if($itemid = $params->get($type))
        {
            $db		= JFactory::getDbo();
            $query	= $db->getQuery(true);

            $query->select($db->nameQuote('link'));
            $query->from($db->nameQuote('#__menu'));
            $query->where($db->nameQuote('published').'=1');
            $query->where($db->nameQuote('id').'='.$db->quote($itemid));

            $db->setQuery($query);

            if($link = $db->loadResult())
            {
                $url = JRoute::_($link.'&Itemid='.$itemid, false);
            }
        }

        if( ! $url)
        {
            // stay on the same page
            $uri = JFactory::getURI();
            $url = $uri->toString(array('path', 'query', 'fragment'));
        }

        return base64_encode($url);
    }

    public static function getType()
    {
        $user = JFactory::getUser();

        return ( ! $user->get('guest')) ? 'logout' : 'login';
    }
}

/**
 * Enter description here ...
 */
class TalkPHP_Gravatar
{
    private $m_szEmail;

    private $m_iSize;

    private $m_szRating;

    private $m_szDefaultImage;

    const GRAVATAR_SITE_URL = 'http://www.gravatar.com/avatar/%s.jpg?s=%s&amp;r=%s&amp;d=%s';

    public function __construct()
    {
        $this->m_iSize = 80;
        $this->m_szRating = 'R';
        $this->m_szDefaultImage = '';
    }

    public function __toString()
    {
        return $this->getAvatar();
    }

    public function getAvatar()
    {
        return sprintf(
        self::GRAVATAR_SITE_URL,
        $this->m_szEmail,
        $this->m_iSize,
        $this->m_szRating,
        $this->m_szDefaultImage
        );
    }

    public function setDefaultImageAsIdentIcon()
    {
        $this->m_szDefaultImage = 'identicon';

        return $this;
    }

    public function setDefaultImageAsMonsterId()
    {
        $this->m_szDefaultImage = 'monsterid';

        return $this;
    }

    public function setDefaultImageAsWavatar()
    {
        $this->m_szDefaultImage = 'wavatar';

        return $this;
    }

    public function setEmail($szEmail)
    {
        $this->m_szEmail = md5($szEmail);

        return $this;
    }

    public function setSize($iSize)
    {
        $this->m_iSize = (int)$iSize;

        return $this;
    }

    public function setRatingAsG()
    {
        $this->m_szRating = 'G';

        return $this;
    }

    public function setRatingAsPG()
    {
        $this->m_szRating = 'PG';

        return $this;
    }

    public function setRatingAsR()
    {
        $this->m_szRating = 'R';

        return $this;
    }

    public function setRatingAsX()
    {
        $this->m_szRating = 'X';

        return $this;
    }
}
