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
 * session driver, i.e. native or database
 *
 * for security reasons the following settings are automatically set:
 * // forces php to manage session ID with cookies only so that this
 * $_GET['PHPSESSID'] is never possible
 * ini_set('session.use_only_cookies', true);
 * // avoids leaking of session ID in URIs
 * ini_set('session.use_trans_sid', false);
 *
 * native: uses PHP native session handling
 *
 * database: session data is securely stored in a database
 * database schema below should work for postgresql and mysql might also work for other db vendors
 *
CREATE TABLE sessions
(
    session_id varchar(64) PRIMARY KEY,
    access bigint NOT NULL,
    data text NOT NULL
);
*
*/
$config['driver'] = 'database';

/**
 * if database driver is used, which connection ?
 * @see database.php
 */
$config['connection'] = 'default';

/**
 * session name
 */
$config['name'] = 'HAYATESESID';

/**
 * encrypt session data
 * NOTE: works only when driver is database
 */
$config['encrypt'] = true;

/**
 * expiration time in seconds or 0 to keep session until browser is
 * closed or up to 24h.
 * (60*60=3600 = 1h)
 */
$config['lifetime'] = 0;

/**
 * path to set in the session cookie, single slash / for the whole
 * domain
 */
$config['path'] = '/';

/**
 * domain under which the session should be readable, i.e. your
 * domain. Prepend the domain with a dot .example.com to include
 * subdomains
 *
 * Note: the domain must be a valid internet domain
 * i.e. "hayatephp.com or .hayatephp.com" and not "hayatephp or
 * .hayatephp" if the provided domain does not have a top level domain
 * (i.e. *.com, *.net, *.co.jp etc.)  an empty domain will be used
 */
$config['domain'] = 'hayate';

/**
 * secure: If TRUE cookie will only be sent over secure connections
 * (i.e. https)
 */
$config['secure'] = false;

/**
 * Marks the cookie as accessible only through the HTTP protocol. This
 * means that the cookie won't be accessible by scripting languages,
 * such as JavaScript. This setting can effectively help to reduce
 * identity theft through XSS attacks (although it is not supported by
 * all browsers).
 */
$config['httponly'] = false;

