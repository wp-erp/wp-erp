Feature('Announcement');

Scenario('@Announcement Publishing an Announcement', ({ I }) => {
I.loginAsAdmin();
I.click('WP ERP')
I.click('HR')
I.click('//*[@id="wpbody-content"]/div[2]/ul/li[5]/a')
I.click('//*[@id="wpbody-content"]/div[3]/a')
I.click('//*[@id="title"]')
I.fillField('Add title','Test66')
I.click('/html/body')
I.type('Rinky_automation')
I.click('//*[@id="hr_announcement_assign_type"]')
I.click('//*[@id="hr_announcement_assign_type"]/option[2]')
I.wait(3)
I.forceClick('Publish')

});
