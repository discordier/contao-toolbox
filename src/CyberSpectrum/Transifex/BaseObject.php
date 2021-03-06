<?php

/**
 * This file is part of cyberspectrum/contao-toolbox.
 *
 * (c) 2013-2017 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/contao-toolbox.
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2013-2017 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/contao-toolbox/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace CyberSpectrum\Transifex;

use Guzzle\Http\EntityBodyInterface;

/**
 * This class provides a base implementation of a transifex object.
 */
class BaseObject
{
    /**
     * The transifex client.
     *
     * @var Transport
     */
    private $transport;

    /**
     * Create a new instance.
     *
     * @param Transport $transport The transport to use.
     */
    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Retrieve the transport in use.
     *
     * @return Transport
     */
    protected function getTransport()
    {
        return $this->transport;
    }

    /**
     * Ensure a parameter exists and return its value.
     *
     * @param string $name The parameter name.
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException When the parameter is missing.
     */
    protected function ensureParameter($name)
    {
        if (!isset($this->$name)) {
            throw new \InvalidArgumentException(get_class($this) . ' is missing parameter: ' . $name);
        }

        return $this->$name;
    }

    /**
     * Post a command to the API.
     *
     * @param string        $command         The command to post.
     *
     * @param null|string[] $params          The parameters (if any).
     *
     * @param string        $postcontenttype The content type.
     *
     * @return EntityBodyInterface|null|string
     */
    protected function post($command, $params = null, $postcontenttype = 'application/json')
    {
        return $this->transport->post($command, $params, $postcontenttype);
    }

    /**
     * Put some data to the API.
     *
     * @param string        $command        The command to post.
     *
     * @param null|string[] $params         The parameters (if any).
     *
     * @param string        $putcontenttype The content type.
     *
     * @return EntityBodyInterface|null|string
     */
    protected function put($command, $params = null, $putcontenttype = 'application/json')
    {
        return $this->transport->put($command, $params, $putcontenttype);
    }

    /**
     * Execute a command on the API.
     *
     * @param string        $command The command to post.
     *
     * @param null|string[] $params  The parameters (if any).
     *
     * @return EntityBodyInterface|null|string
     */
    protected function execute($command, $params = null)
    {
        return $this->transport->execute($command, $params);
    }

    /**
     * Execute a command on the API and return the content as json decoded array..
     *
     * @param string        $command The command to post.
     *
     * @param null|string[] $params  The parameters (if any).
     *
     * @return array
     */
    protected function executeJson($command, $params = null)
    {
        return $this->transport->executeJson($command, $params);
    }
}
