<?php
namespace Tests\Unit;

use Actinity\Actinite\Services\Parser;
use Tests\TestCase;

class ParserTest
	extends TestCase
{
	public function basic_test()
	{
		$this->assertEquals("<p>Hello World</p>",$this->html("<p>Hello World</p>"));
	}

	public function test_whitespace()
	{
		// We wouldn't normally care about whitespace, but it'll make testing easier to standardise it.
		$this->assertEquals("<p>Hello World</p>",$this->html("<p>\n\tHello\n World\n</p>"));
	}

	public function test_a_non_html_field_has_tags_stripped()
	{
		$this->assertEquals('Hello World',Parser::input(['type' => 'text'],'<p>Hello World</p>'));
	}

	public function test_script_tags_are_nuked()
	{
		$this->assertEquals('Hi alert(1);',$this->html('Hi <script>alert(1);</script>'));
	}

	public function test_an_asset_url_is_replaced()
	{
		$this->assertEquals('<img src="cms://assets/1" data-ac-asset="1">',$this->html('<img src="about:blank" data-ac-asset="1">'));

	}





	private function html($content, $field = []): string
	{
		$field = array_merge(['type' => 'html'],$field);
		$content = Parser::input($field,$content);
		$content = preg_replace("!>\s+!",">",$content);
		$content = preg_replace("!\s+<!","<",$content);
		return preg_replace("!\s+!"," ",$content);
	}
}