<?php

namespace slackbot\helpers;


class TimeAgoHelper
{
    public function format($givenTime) {
        $estimatedTime = time() - $givenTime;

        if( $estimatedTime < 1 ) {
            return 'less than 1 second ago';
        }

        $periods = array(
            12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        );

        foreach( $periods as $secs => $str ) {
            $d = $estimatedTime / $secs;

            if( $d >= 1 ) {
                $r = round( $d );
                return $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
            }
        }
    }
}
