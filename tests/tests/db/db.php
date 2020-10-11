<?php

/**
 * DB instance test
 *
 * @group db
 */
class DB_Set_Tests extends PHPUnit_Framework_TestCase {

    protected $ydb_copy = null;

    /**
     * Make a copy of $ydb
     */
    public function setUp() {
        $this->ydb_copy = yourls_get_db();
        yourls_set_db(null);
    }

    /**
     * Restore original $ydb
     */
    public function tearDown() {
        yourls_set_db($this->ydb_copy);
    }

    public function test_get() {
        $this->assertInstanceOf( '\YOURLS\Database\YDB', yourls_get_db() );
    }

    public function test_set() {
        yourls_set_db("hello");
        $this->assertSame( "hello", yourls_get_db() );
    }

    /**
     * Note to self : I'm unable to write a test to check that yourls_get_db(null)
     * actually unsets $ydb. It seems I'm hitting the limits to my understandings
     * of PHPUnit and global vars.
     *
     * For the record, the following doesn't work:
     *
     * public function test_unset() {
     *     global $ydb;
     *     $this->assertTrue( isset($ydb) );
     *     yourls_set_db(null);
     *     $this->assertFalse( isset($ydb) );
     * }
     *
     * Oh well. ¯\_(ツ)_/¯
     *
     */


}
