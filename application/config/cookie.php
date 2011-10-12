<?php
/**
* Hayate Framework
* Copyright 2009 Andrea Belvedere
*
* Hayate is free software: you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation, either
* version 3 of the License, or (at your option) any later version.
*
* This software is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @see http://php.net/manual/en/function.setcookie.php
 */

/**
 * expire time
 *
 * number of seconds the cookie should last or if set to 0 the cookie
 * will expire at the end of the session (when the browser closes).
 */
$config['expire'] = 0;

/**
 * cookie path
 *
 * path within the server the cookie is available set to '/' for the
 * entire site
 */
$config['path'] = '/';

/**
 * the domain
 *
 * domain that the cookie is available, prefixing the domain with a
 * dot .example.com will make the cookie available to all subdomains
 * while without a . cookie will be available only for that domain
 */
$config['domain'] = 'hayate';

/**
 * secure
 *
 * set to true indicates that the client should only send the cookie
 * back over a secure httpS connection
 */
$config['secure'] = false;

/**
 * encrypt
 *
 * true to store encrypted cookies false otherwise
 */
$config['encrypt'] = true;

/**
 * httponly
 *
 * not supported by all browser, however when set to true the cookie
 * will only be accessible via http protocol i.e. cookies will not be
 * accessible by scripting languages such as JavaScript
 */
$config['httponly'] = false;
