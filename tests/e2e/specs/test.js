// For authoring Nightwatch tests, see
// http://nightwatchjs.org/guide#usage
// ./node_modules/.bin/nightwatch --test tests/e2e/test.js --config ./nightwatch.json


module.exports = {

  before: function (browser) {
    console.log('Setting up... browser', typeof browser)
  },

  after: function (browser) {
    console.log('Closing down... browser', typeof browser)
  },

  'CoreUI Vue e2e tests': function (browser) {
    // automatically uses dev Server port from /config.index.js
    // default: http://localhost:8080
    // see nightwatch.conf.js

    // const devServer = browser.globals.devServerURL
    //const devServer = process.env.VUE_DEV_SERVER_URL

    const url = 'http://localhost:8000/#/tables/lazy-loading-tables';

    browser.url(url).pause(2000).expect.element('body').to.be.present

    browser.waitForElementVisible('.c-app', 2000)
      .assert.elementPresent('.c-header')
      .assert.elementPresent('.c-sidebar')
      .assert.elementPresent('.c-footer')
      .assert.elementPresent('.c-sidebar')
      .assert.elementPresent('.c-body')
    browser.waitForElementVisible('.table', 2000) 
    browser.elements('css selector', '.table tr', (result) => {    
        if (result.value.length !== 13) { //(10.rows + 3.header.rows)
            browser.assert.fail('Number of elements is not correct - ' + result.value.length);
        }
    });
    /* Change number of items in table test */
    browser.click('select[class=form-control]').keys(['\uE015', '\uE006'])
    browser.waitForElementVisible('.table', 2000) 
    browser.elements('css selector', '.table tr', (result) => {    
        if (result.value.length !== 8) { //(5.rows + 3.header.rows)
            browser.assert.fail('Number of elements is not correct - ' + result.value.length);
        }
    });
    /* Test Sorting Table */
    browser.pause(1000);
    browser.useXpath();
    //browser.click('//table/thead//svg[2]');  //click in svg not working!
    //browser.click('//table/thead/tr[2]/th[2]/input');
    setValue('//table/thead/tr[2]/th[2]/input', 'A');
    browser.click('//table/tbody/tr[1]/td[2]');
    browser.pause(2000);
    browser.assert.containsText('//table/tbody/tr[1]/td[2]', "A a");

    //browser.assert.containsText('.table tr td:nth-of-type(2)', "A a")
    //browser.click('.table tr svg');
    //browser.assert.containsText('.table tr td:nth-of-type(2)', "A a")

/*
    browser.elements('css selector', '.table tr td:nth-of-type(1)', (result) => {    
        result.value[0].
        browser.assert.fail('Number of elements is not correct - ' + result.value);
        
    });
*/

    browser.pause(2000);
    browser.end()
  }
}
