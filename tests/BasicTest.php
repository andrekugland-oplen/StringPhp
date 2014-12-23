<?php

class BasicTest extends PHPUnit_Framework_TestCase
{

  public function testConstructors() {
    $this->assertInstanceOf('S', S('a'));
    $this->assertSame(S('a')->s, 'a');
    $this->assertSame(S("\xe1", 'iso-8859-1')->s, 'á');
  }

  public function testToString() {
    $this->assertEquals(S('a'), 'a');
    $this->assertNotSame(S('a'), 'a');
  }

  public function testSerialize() {
    $this->assertInstanceOf('Serializable', S('a'));
    $this->assertEquals(unserialize(serialize(S('a'))), S('a'));
    $this->assertInstanceOf('JsonSerializable', S('a'));
    $this->assertSame(json_decode(json_encode(S('a'))), 'a');
  }

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

  public function testBasic() {
    $this->assertSame(S('διονύσιος ὁ ἀρεοπαγίτες')->capitalize()->s, 'Διονύσιος ὁ ἀρεοπαγίτες');
    $this->assertSame(S('Достоевский')->charAt(10), 'й');
    $this->assertSame(S('Достоевский')->charCodeAt(10), 0x0439);
    $this->assertLessThan(S('001')->cmp('000'), 0);
    $this->assertSame(S('001')->cmp('001'), 0);
    $this->assertGreaterThan(S('001')->cmp('002'), 0);
    $this->assertSame(S('a')->concat('b')->s, 'ab');
    $this->assertTrue(S('abcd')->contains('bc'));
    $this->assertFalse(S('abcd')->contains('ef'));
    $this->assertSame(S('a')->copy()->s, 'a');
    $this->assertSame(S('a,b,c,d')->count(','), 3);
    $this->assertTrue(S('abcd')->endsWith('cd'));
    $this->assertFalse(S('abcd')->endsWith('bc'));
    $this->assertSame(S('/root/')->ensureLeft('/')->s, '/root/');
    $this->assertSame(S('root/')->ensureLeft('/')->s, '/root/');
    $this->assertSame(S('/root/')->ensureRight('/')->s, '/root/');
    $this->assertSame(S('/root')->ensureRight('/')->s, '/root/');
    $this->assertTrue(S('abcd')->equals('abcd'));
    $this->assertFalse(S('abcd')->equals('cd'));
    $this->assertGreaterThan(S('abcd')->icmp('ACDE'), 0);
    $this->assertSame(S('abcd')->icmp('ABCD'), 0);
    $this->assertLessThan(S('acde')->icmp('ABCD'), 0);
    $this->assertTrue(S('ÁéÍóÚ')->iequals('áéíóú'));
    $this->assertSame(S('ÁéÍóÚ')->indexOf('é'), 1);
    $this->assertSame(S('ÁéÍóÚ')->indexOf('z'), -1);
    $this->assertSame(S(',')->join(array('1', '2', '3'))->s, "1,2,3");
    $this->assertSame(S('ÁéÍóÚ')->lastIndexOf('é'), 1);
    $this->assertSame(S('ÁéÍóÚ')->lastIndexOf('z'), -1);
    $this->assertSame(S('aabb')->left(2)->s, 'aa');
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
    $this->assertTrue(S('aabb')->startsWith('aa'));
    $this->assertFalse(S('aabb')->startsWith('bb'));
    $this->assertSame(S('a,b,c')->split(','), array('a', 'b', 'c'));
    $this->assertSame(S(',a,b,c,')->split(','), array('', 'a', 'b', 'c', ''));
    $this->assertSame(S('a,b,c,')->split(',', -1, true), array('a', 'b', 'c'));
    $this->assertSame(S('a,b,c,d')->strip(',')->s, 'abcd');
    $this->assertSame(S('a,b,c,d')->strip(',', 2)->s, 'abc,d');
    $this->assertSame(S('a')->times(4)->s, 'aaaa');
    $this->assertSame(S('ǌam')->title()->s, 'ǋam');
    $this->assertSame(S('ßam')->title()->s, 'Ssam');
    $this->assertSame(S('διονύσιος ὁ ἀρεοπαγίτες')->title()->s, 'Διονύσιος Ὁ Ἀρεοπαγίτες');
    $this->assertSame(S('ebg13 vf vafrpher')->translate('abefghprv', 'norstucei')->s, 'rot13 is insecure');
    $this->assertSame(S('ǌam')->upper()->s, 'ǊAM');
  }

  public function testWhitespace() {
    $this->assertSame(S('a  b  c')->collapseWhitespace()->s, 'a b c');
    $this->assertSame(S("a \xc2\xa0 \t b \xc2\xa0 \t c")->collapseWhitespace()->s, 'a b c');
    $this->assertSame(S('abc')->padCenter(5)->s, ' abc ');
    $this->assertSame(S('abc')->padLeft(5)->s, '  abc');
    $this->assertSame(S('abc')->padRight(5)->s, 'abc  ');
    $this->assertSame(S('abc')->padCenter(5,'á')->s, 'áabcá');
    $this->assertSame(S('abc')->padLeft(5,'á')->s, 'ááabc');
    $this->assertSame(S('abc')->padRight(5,'á')->s, 'abcáá');
    $this->assertSame(S("a\nb")->toDos()->s, "a\r\nb");
    $this->assertSame(S("a\r\nb")->toDos()->s, "a\r\nb");
    $this->assertSame(S("a\r\nb")->toUnix()->s, "a\nb");
    $this->assertSame(S("\t\xc2\xa0 ab \xc2\xa0\t")->trim()->s, "ab");
    $this->assertSame(S("\t\xc2\xa0 \xc2\xa0\t")->trim()->s, "");
    $this->assertSame(S("\t\xc2\xa0 ab \xc2\xa0\t")->trimLeft()->s, "ab \xc2\xa0\t");
    $this->assertSame(S("\t\xc2\xa0 \xc2\xa0\t")->trimRight()->s, '');
    $this->assertSame(S("\t\xc2\xa0  ab \xc2\xa0\t")->trimRight()->s, "\t\xc2\xa0  ab");
    $this->assertSame(S("\t\xc2\xa0 \xc2\xa0\t")->trimRight()->s, '');
  }

  public function testTestAscii() {
    $blankAscii = array(9, 32);

    $punctAscii = array(
       33,  34,  35,  36,  37,  38,  39,  40,
       41,  42,  43,  44,  45,  46,  47,  58,
       59,  60,  61,  62,  63,  64,  91,  92,
       93,  94,  95,  96, 123, 124, 125, 126
    );

    for ($i = 0; $i < 256; $i++) {
      $this->assertEquals(S::fromCharCode($i)->isAlpha(false), S::fromCharCode($i)->countRegex('/[A-Za-z]/') == 1);
      $this->assertEquals(S::fromCharCode($i)->isAlnum(false), S::fromCharCode($i)->countRegex('/[0-9A-Za-z]/') == 1);
      $this->assertEquals(S::fromCharCode($i)->isAscii(false), $i < 128);
      $this->assertEquals(S::fromCharCode($i)->isBlank(false), in_array($i, $blankAscii));
      $this->assertEquals(S::fromCharCode($i)->isCntrl(false), $i < 32 || $i === 127);
      $this->assertEquals(S::fromCharCode($i)->isLower(false), S::fromCharCode($i)->countRegex('/[a-z]/') == 1);
      $this->assertEquals(S::fromCharCode($i)->isNumber(false), S::fromCharCode($i)->countRegex('/[0-9]/') == 1);
      $this->assertEquals(S::fromCharCode($i)->isUpper(false), S::fromCharCode($i)->countRegex('/[A-Z]/') == 1);
      $this->assertEquals(S::fromCharCode($i)->isPunct(false), in_array($i, $punctAscii));
    }
  }

}
