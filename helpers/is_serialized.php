<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */ 

/**
 * Thanks to Chris for great solution, i have add only small changes
 */

/**
 * Tests if an input is valid PHP serialized string.
 *
 * Checks if a string is serialized using quick string manipulation
 * to throw out obviously incorrect strings. Unserialize is then run
 * on the string to perform the final verification.
 *
 * Valid serialized forms are the following:
 * <ul>
 * <li>boolean: <code>b:1;</code></li>
 * <li>integer: <code>i:1;</code></li>
 * <li>double: <code>d:0.2;</code></li>
 * <li>string: <code>s:4:"test";</code></li>
 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
 * <li>null: <code>N;</code></li>
 * </ul>
 *
 * @author  Chris Smith <code+php@chris.cs278.org>, Frank Bültge <frank@bueltge.de>
 * @copyright   Copyright (c) 2009 Chris Smith (http://www.cs278.org/), 2011 Frank Bültge (http://bueltge.de)
 * @license http://sam.zoy.org/wtfpl/ WTFPL
 * @param   string  $value  Value to test for serialized form
 * @param   mixed   $result Result of unserialize() of the $value
 * @return  boolean         True if $value is serialized data, otherwise FALSE
 */
function is_serialized( $value, $result = null ) {
    // Bit of a give away this one
    if ( ! is_string( $value ) ) {
        return FALSE;
    }

    // Serialized FALSE, return TRUE. unserialize() returns FALSE on an
    // invalid string or it could return FALSE if the string is serialized
    // FALSE, eliminate that possibility.
    if ( 'b:0;' === $value ) {
        $result = FALSE;
        return TRUE;
    }

    $length = strlen($value);
    $end    = '';
    
    if ( isset( $value[0] ) ) {
        switch ($value[0]) {
            case 's':
                if ( '"' !== $value[$length - 2] )
                    return FALSE;
                
            case 'b':
            case 'i':
            case 'd':
                // This looks odd but it is quicker than isset()ing
                $end .= ';';
            case 'a':
            case 'O':
                $end .= '}';
    
                if ( ':' !== $value[1] )
                    return FALSE;
    
                switch ( $value[2] ) {
                    case 0:
                    case 1:
                    case 2:
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                    case 9:
                    break;
    
                    default:
                        return FALSE;
                }
            case 'N':
                $end .= ';';
            
                if ( $value[$length - 1] !== $end[0] )
                    return FALSE;
            break;
            
            default:
                return FALSE;
        }
    }
    
    if ( ( $result = @unserialize($value) ) === FALSE ) {
        $result = null;
        return FALSE;
    }
    
    return TRUE;
}

?>