<?php
    if ( ! isset( Config::$_strAlien ) ) {
        echo "Please include Config.php first!";
        exit( 500 );
    }
    
    require_once( Config::$_strAlien . '/SystemFunctions.php' );
    require_once( Config::$_strAlien . '/Log.php' );
    
    if ( ! empty( Config::$_DB_DB ) ) {
        require_once( Config::$_strAlien . '/DbFunctions.php' );
    }
    
    class 👽
    {
        
        static function init() {
            error_reporting( E_ALL );
            //self::initErrorHandler();
            if ( ! empty( Config::$_DB_DB ) ) {
                if ( ! isset( Config::$_DB_CONNECT_AUTOMATICLY ) || Config::$_DB_CONNECT_AUTOMATICLY ) {
                    dbConnector();
                }
            }
            if ( ! empty( $_SERVER[ 'REQUEST_METHOD' ] ) && $_SERVER[ 'REQUEST_METHOD' ] === 'OPTIONS' ) {
                header( "HTTP/1.1 200 OK" );
                self::exitNowWithoutExtraHeaderAndBody( 'REQUEST_METHOD: OPTION!' );
            }
        }
        
        static function 🖖() {
            return call_user_func_array( "👽::exitNow", func_get_args() );
        }
        
        /**
         * @param string $strLastLogMessage
         */
        static function exitNowWithoutExtraHeaderAndBody( $strLastLogMessage = '' ) {
            Log::$_strLastLogMessage = $strLastLogMessage;
            
            $fLog = true;
            if ( isset( Config::$_fDebug ) && empty( Config::$_fDebug ) ) { // explicit set fDebug = false?
                $fLog = false;
                if ( ! empty( Config::$_fDebugOnError ) && ! empty( Log::$_nErrors ) ) {
                    // if Config::$_fDebug == false and Config::$_fDebugOnError = 1 and Errors
                    $fLog = true;
                }
            }
            
            if ( ! empty( Config::$_strDebugLog ) ) {
                if ( $fLog ) Log::toLogFile();
            }
            
            if ( ! empty( Config::$_DB_DEBUG_TABLE ) ) {
                if ( $fLog ) Log::toDatabase();
            }
            
            exit();
        }
        
        /**
         * @param string $strLastLogMessage
         * @param int $nHttpErrCode
         * @param string $strOutput
         */
        static function exitNow( $strLastLogMessage = '', $nHttpErrCode = 0, $strOutput = "" ) {
            
            if ( $nHttpErrCode ) {
                if ( $nHttpErrCode >= 200 && $nHttpErrCode < 300 ) {
                    header( "HTTP/1.0 $nHttpErrCode" );
                } else {
                    // Log::error( $strLastLogMessage, $strMethod, $strLine, $strFile );
                    header( "HTTP/1.0 $nHttpErrCode $strLastLogMessage" );
                }
            }
            
            if ( $strOutput ) echo $strOutput;
            
            self::exitNowWithoutExtraHeaderAndBody( $strLastLogMessage );
        }
        
        /**
         * All php errors and warnings will be logged.
         */
        static function initErrorHandler() {
            error_reporting( E_ALL );
            set_error_handler( "👽::theErrorHandler" );
        }
        
        /**
         * Do not call this function directly!
         * @param $errfile
         * @param $errline
         * @param $errno
         * @param $errstr
         * @see self::initErrorHandler()
         */
        static function theErrorHandler( $errno, $errstr, $errfile, $errline ) {
            
            switch ( $errno ) {
                case E_USER_ERROR:
                    Log::error( "E_USER_ERROR: $errstr", $errfile, $errline );
                    break;
                
                case E_USER_WARNING:
                    Log::error( "E_USER_WARNING: $errstr", $errfile, $errline );
                    break;
                
                case E_WARNING:
                    Log::error( "E_WARNING: $errstr", $errfile, $errline );
                    break;
                
                case E_USER_NOTICE:
                case E_NOTICE:
                    break;
                
                default:
                    Log::error( "default: $errstr", $errfile, $errline );
                    //print_r($ctx) ;
                    break;
            }
            return true;
        }
        
        static function strStringBetween( $strAll, $strSub1, $strSub2, $nStartSrchPos = 0 ) {
            if ( ! $strSub1 || ! $strSub2 || ! $strAll ) return '';
            $nLen = strlen( $strSub1 );
            $nPos1 = strpos( $strAll, $strSub1, $nStartSrchPos );
            $nPos2 = ( $nPos1 === false ) ? false : strpos( $strAll, $strSub2, ( $nPos1 + $nLen ) );
            return $nPos2 ? substr( $strAll, $nPos1 + $nLen, $nPos2 - $nPos1 - $nLen ) : '';
        }
        
        static function strReplaceBetween( $strAll, $strSub1, $strSub2, $strNew, $nStartSrchPos = 0 ) {
            if ( ! $strSub1 || ! $strSub2 || ! $strAll ) return $strAll;
            $nLen = strlen( $strSub1 );
            $nPos1 = strpos( $strAll, $strSub1, $nStartSrchPos );
            $nPos2 = ( $nPos1 === false ) ? false : strpos( $strAll, $strSub2, ( $nPos1 + $nLen ) );
            return $nPos2 ? substr( $strAll, 0, $nPos1 + $nLen ) . $strNew . substr( $strAll, $nPos2 ) : $strAll;
        }
        
        /**
         * @param int $nType 1:= Y-m-d, 2:= timestamp, 3:= Y-m-d H:i:s, 4:= array(Y,m,d,H,i,s), 5:=DD.MM.YYYY, 6:=MM/DD/YYYY, 7:=5|6DepOnOLanguageCd,8=ISO
         * @param string $strD YYYYMMDD or YYYYMMDDHHMMSS or YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
         * @return string|array
         */
        static function strDateTo( $nType, $strD ) {
            $l = strlen( $strD );
            if ( $nType < 1 or $nType > 8 or ( $l != 8 and $l != 14 and $l != 10 and $l != 19 ) ) {
                Log::error( "invalid params! ($nType, $strD)" );
                return $strD;
            }
            // if YYYY-MM-DD or YYYY-MM-DD HH:MM:SS?
            if ( $l == 10 || $l == 19 ) {
                $strD = substr( $strD, 0, 4 ) . substr( $strD, 5, 2 ) . substr( $strD, 8, 2 )
                    . ( $l == 19 ? substr( $strD, 11, 2 ) . substr( $strD, 14, 2 ) . substr( $strD, 17, 2 ) : '' );
                $l = strlen( $strD );
            }
            // now $strDate is YYYYMMDD or YYYYMMDDHHMMSS
            if ( $nType == 7 && function_exists( "o" ) ) $nType = ( o( 'languageCd' ) == 'de' ? 5 : 6 );
            if ( $nType == 1 ) return substr( $strD, 0, 4 ) . '-' . substr( $strD, 4, 2 ) . '-' . substr( $strD, 6, 2 );
            if ( $nType == 2 ) return strtotime( self::strDateTo( 3, $strD ) );
            if ( $nType == 8 ) return gmdate( 'Y-m-d\TH:i:s\Z', strtotime( self::strDateTo( 3, $strD ) ) );
            if ( $nType == 3 ) return substr( $strD, 0, 4 ) . '-' . substr( $strD, 4, 2 ) . '-' . substr( $strD, 6, 2 )
                . ( $l == 8 ? ' 00:00:00' : ' ' . substr( $strD, 8, 2 ) . ':' . substr( $strD, 10, 2 ) . ':' . substr( $strD, 12, 2 ) );
            if ( $nType == 4 ) return array( 0 + substr( $strD, 0, 4 ), 0 + substr( $strD, 4, 2 ), 0 + substr( $strD, 6, 2 ),
                0 + substr( $strD, 8, 2 ), 0 + substr( $strD, 10, 2 ), 0 + substr( $strD, 12, 2 ) );
            if ( $nType == 5 ) return substr( $strD, 6, 2 ) . '.' . substr( $strD, 4, 2 ) . "." . substr( $strD, 0, 4 );
            if ( $nType == 6 ) return substr( $strD, 4, 2 ) . '/' . substr( $strD, 6, 2 ) . "/" . substr( $strD, 0, 4 );
            return 'strDateTo(error)';
        }
        
        static function strEndsWith( $strBig, $parts ) {
            $strBig = strtolower( $strBig );
            if ( is_array( $parts ) ) {
                foreach ( $parts as $str ) if ( self::strEndsWith( $strBig, $str ) ) return true;
            } elseif ( ( $parts && is_string( $parts ) ) || is_numeric( $parts ) ) {
                $l = strlen( $strBig );
                $l2 = strlen( $parts );
                return $l2 < $l && substr( $strBig, -1 * $l2 ) == strtolower( $parts );
            }
            return false;
        }
        
        static function strStartsWith( $strBig, $parts ) {
            $strBig = strtolower( $strBig );
            if ( is_array( $parts ) ) {
                foreach ( $parts as $str ) if ( self::strStartsWith( $strBig, $str ) ) return true;
            } elseif ( ( $parts && is_string( $parts ) ) || is_numeric( $parts ) ) {
                return strtolower( $parts ) == substr( $strBig, 0, strlen( $parts ) );
            }
            return false;
        }
        
        static function arraysTrimValues( $arr ) {
            if ( is_array( $arr ) ) foreach ( $arr as $k => $v ) $arr[ $k ] = trim( $v );
            return $arr;
        }
        
    }
    
    👽::init();
    class_alias( '👽', 'Alien' );

