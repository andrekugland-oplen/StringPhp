<?php

class BasicTest extends PHPUnit_Framework_TestCase
{
	public function testConstructors() {
		$this->assertInstanceOf('S', S('a'));
		$this->assertSame(S('a')->s, 'a');
		$this->assertSame(S("\xe1", 'iso-8859-1')->s, 'á');
	}

	/**
	 * @depends testConstructors
	 */
	public function testToString() {
		$this->assertEquals(S('a'), 'a');
		$this->assertNotSame(S('a'), 'a');
	}

	/**
	 * @depends testConstructors
	 */
	public function testSerialize() {
		$this->assertInstanceOf('Serializable', S('a'));
		$this->assertEquals(unserialize(serialize(S('a'))), S('a'));
		$this->assertInstanceOf('JsonSerializable', S('a'));
		$this->assertSame(json_decode(json_encode(S('a'))), 'a');
	}

	/**
	 * @depends testSerialize
	 */
	public function testConversions() {
		$this->assertSame(S::fromCharArray(array('道','德','經'))->s, '道德經');
		$this->assertSame(S::fromCharCode(0x9053)->s, '道');
		$this->assertSame(S::fromCharCodeArray(array(0x9053, 0x5fb7, 0x7d93))->s, '道德經');
		$this->assertSame(S::fromJson('"áéíóú"')->s, 'áéíóú');
		$this->assertSame(S::fromNumber(0x0abc, 16, 4)->s, '0abc');
		$this->assertEquals(S('道德經')->toCharArray(), array('道','德','經'));
		$this->assertEquals(S('道德經')->toCharCodeArray(), array(0x9053, 0x5fb7, 0x7d93));
		$this->assertSame(S('abc')->toJson(), json_encode('abc'));
		$this->assertSame(S('ab/cd')->toRegex(), "/ab\\/cd/");
	}

	/**
	 * @depends testConversions
	 */
	public function testBasic() {
		$this->assertSame(S('διονύσιος ὁ ἀρεοπαγίτες')->capitalize()->s, 'Διονύσιος ὁ ἀρεοπαγίτες');
		$this->assertSame(S('Достоевский')->charAt(10), 'й');
		$this->assertSame(S('Достоевский')->charCodeAt(10), 0x0439);
		$this->assertLessThan(S('001')->cmp('000'), 0);
		$this->assertSame(S('001')->cmp('001'), 0);
		$this->assertGreaterThan(S('001')->cmp('002'), 0);
		$this->assertSame(S('a')->concat('b')->s, 'ab');
		$this->assertSame(S('abcd')->contains('bc'), true);
		$this->assertSame(S('abcd')->contains('ef'), false);
		$this->assertSame(S('a')->copy()->s, 'a');
		$this->assertSame(S('a,b,c,d')->count(','), 3);
		$this->assertSame(S('abcd')->endsWith('cd'), true);
		$this->assertSame(S('abcd')->endsWith('bc'), false);
		$this->assertSame(S('/root/')->ensureLeft('/')->s, '/root/');
		$this->assertSame(S('root/')->ensureLeft('/')->s, '/root/');
		$this->assertSame(S('/root/')->ensureRight('/')->s, '/root/');
		$this->assertSame(S('/root')->ensureRight('/')->s, '/root/');
		$this->assertSame(S('abcd')->equals('abcd'), true);
		$this->assertSame(S('abcd')->equals('cd'), false);
		$this->assertGreaterThan(S('abcd')->icmp('ACDE'), 0);
		$this->assertSame(S('abcd')->icmp('ABCD'), 0);
		$this->assertLessThan(S('acde')->icmp('ABCD'), 0);
		$this->assertSame(S('ÁéÍóÚ')->iequals('áéíóú'), true);
		$this->assertSame(S('ÁéÍóÚ')->indexOf('é'), 1);
		$this->assertSame(S('ÁéÍóÚ')->indexOf('z'), -1);
		$this->assertSame(S(',')->join(array('1', '2', '3'))->s, "1,2,3");
		$this->assertSame(S('ÁéÍóÚ')->lastIndexOf('é'), 1);
		$this->assertSame(S('ÁéÍóÚ')->lastIndexOf('z'), -1);
		$this->assertSame(S('aabb')->left( 2)->s, 'aa');
		$this->assertSame(S('aabb')->left(-2)->s, 'bb');
		$this->assertSame(S('道德經')->length(), 3);
		$this->assertSame(S("a\nb\nc\n\nd")->lines(), array('a', 'b', 'c', '', 'd'));
		$this->assertSame(S("a\nb\nc\n\nd")->lines(2), array('a', "b\nc\n\nd"));
		$this->assertSame(S("a\nb\nc\n\nd")->lines(-1, true), array('a', 'b', 'c', 'd'));
		$this->assertSame(S('ǊAM')->lower()->s, 'ǌam');
		$this->assertSame(S('a')->prefix('_')->s, '_a');
		$this->assertSame(S('aaa')->replace('a', 'b')->s, 'bbb');
		$this->assertSame(S('aaa')->replace('a', 'b', 2)->s, 'bba');
		$this->assertSame(S('ab')->replaceMany(array('a' => 'b', 'b' => 'a'))->s, 'ba');
		$this->assertSame(S('aabb')->right( 2)->s, 'bb');
		$this->assertSame(S('aabb')->right(-2)->s, 'aa');
		$this->assertSame(S('aabb')->slice(0, 2)->s, 'aa');
		$this->assertSame(S('aabb')->slice(-3, 2)->s, 'ab');
		$this->assertSame(S('aabb')->slice(1, 2)->s, 'ab');
		$this->assertSame(S('aabb')->startsWith('aa'), true);
		$this->assertSame(S('aabb')->startsWith('bb'), false);
		$this->assertSame(S('a,b,c')->split(','), array('a', 'b', 'c'));
		$this->assertSame(S(',a,b,c,')->split(','), array('', 'a', 'b', 'c', ''));
		$this->assertSame(S('a,b,c,')->split(',', -1, true), array('a', 'b', 'c'));
		$this->assertSame(S('a')->times(4)->s, 'aaaa');
		$this->assertSame(S('ǌam')->title()->s, 'ǋam');
		$this->assertSame(S('ßam')->title()->s, 'Ssam');
		$this->assertSame(S('ebg13 vf vafrpher')->translate('abefghprv', 'norstucei')->s, 'rot13 is insecure');
		$this->assertSame(S('ǌam')->upper()->s, 'ǊAM');		
	}
}
