describe('AdConfig2', function(){


	it('getProvider failsafe to Later', function() {
		var adProviderNullMock = {name: 'NullMock'}
			, evolveSlotConfigMock = {name: 'EvolveMock', canHandleSlot: function() {return false;}}
			, adProviderDirectGptMock = {name:'GptMock', canHandleSlot: function() {return false;}}
			, adProviderLaterMock = {name: 'LaterMock'}
			, geoMock = {getCountryCode:function() {}}
			, logMock = function() {}
			, windowMock = {wgShowAds: true}
			, documentMock = {}
			, adDecoratorPageDimensionsMock = {isApplicable: function() {return false;}}
			, abTestMock = {inGroup: function() {return false;}}
			, adConfig;

		adConfig = modules['ext.wikia.adEngine.adConfig'](
			logMock,
			windowMock,
			documentMock,
			geoMock,
			abTestMock,
			adDecoratorPageDimensionsMock,
			evolveSlotConfigMock

			// AdProviders
			, adProviderDirectGptMock
			, adProviderLaterMock
			, adProviderNullMock
		);

		expect(adConfig.getProvider(['foo'])).toBe(adProviderLaterMock, 'adProviderLaterMock');
	});

	it('getProvider use GPT for high value slots', function() {
		var adProviderNullMock = {name: 'NullMock'}
			, evolveSlotConfigMock = {canHandleSlot: function() {return false;}}
			, adProviderDirectGptMock = {name:'GptMock', canHandleSlot: function() {return true;}}
			, adProviderLaterMock = {name: 'LaterMock', canHandleSlot: function() {return true;}}
			, geoMock = {getCountryCode: function() {return 'hi-value-country'}}
			, logMock = function() {}
			, windowMock = {wgHighValueCountries: {'hi-value-country': true, 'another-hi-value-country': true}, wgShowAds: true}
			, documentMock = {}
			, adDecoratorPageDimensionsMock = {isApplicable: function() {return false;}}
			, abTestMock = {inGroup: function() {return false;}}
			, adConfig
			, highValueSlot = 'TOP_LEADERBOARD'
			;

		adConfig = modules['ext.wikia.adEngine.adConfig'](
			logMock,
			windowMock,
			documentMock,
			geoMock,
			abTestMock,
			adDecoratorPageDimensionsMock
			, evolveSlotConfigMock

			// AdProviders
			, adProviderDirectGptMock
			, adProviderLaterMock
			, adProviderNullMock
		);

		expect(adConfig.getProvider(['foo'])).toBe(adProviderLaterMock, 'adProviderLaterMock');
		expect(adConfig.getProvider([highValueSlot])).toBe(adProviderDirectGptMock, 'adProviderDirectGptMock');
	});

	it('getProvider use Evolve for NZ (only if provider accepts)', function() {
		var adProviderNullMock = {name: 'NullMock'}
			, evolveSlotConfig = {canHandleSlot: function() {return true;}}
			, adProviderDirectGptMock = {name:'GptMock'}
			, adProviderLaterMock = {name: 'LaterMock'}
			, geoMockAU = {getCountryCode:function() {return 'NZ';}}
			, logMock = function() {}
			, windowMock = {wgShowAds: true}
			, documentMock = {}
			, adDecoratorPageDimensionsMock = {isApplicable: function() {return false;}}
			, abTestMock = {inGroup: function() {return false;}}
			, adConfig;

		adConfig = modules['ext.wikia.adEngine.adConfig'](
			logMock,
			windowMock,
			documentMock,
			geoMockAU,
			abTestMock,
			adDecoratorPageDimensionsMock
			, evolveSlotConfig

			// AdProviders
			, adProviderDirectGptMock
			, adProviderLaterMock
			, adProviderNullMock
		);

		expect(adConfig.getProvider(['foo'])).toBe(adProviderLaterMock, 'adProviderEvolveMock NZ');
	});

	it('getProvider do not use Evolve for PL', function() {
		var adProviderNullMock = {name: 'NullMock'}
			, evolveSlotConfig = {canHandleSlot: function() {return true;}}
			, adProviderDirectGptMock = {name:'GptMock', canHandleSlot: function() {return true;}}
			, adProviderLaterMock = {name: 'LaterMock'}
			, geoMock = {getCountryCode:function() {return 'PL';}}
			, logMock = function() {}
			, windowMock = {wgShowAds: true}
			, documentMock = {}
			, adDecoratorPageDimensionsMock = {isApplicable: function() {return false;}}
			, abTestMock = {inGroup: function() {return false;}}
			, adConfig
			, highValueSlot = 'TOP_LEADERBOARD';

		adConfig = modules['ext.wikia.adEngine.adConfig'](
			logMock,
			windowMock,
			documentMock,
			geoMock,
			abTestMock,
			adDecoratorPageDimensionsMock
			, evolveSlotConfig

			// AdProviders
			, adProviderDirectGptMock
			, adProviderLaterMock
			, adProviderNullMock
		);

		expect(adConfig.getProvider([highValueSlot])).not.toBe(adProviderLaterMock, 'adProviderEvolveMock');
	});

	it('getProvider do not use Evolve for NZ when it cannot handle the slot', function() {
		var adProviderNullMock = {name: 'NullMock'}
			, evolveSlotConfigMock = {canHandleSlot: function() {return false;}}
			, adProviderDirectGptMock = {name:'GptMock', canHandleSlot: function() {return true;}}
			, adProviderLaterMock = {name: 'LaterMock'}
			, geoMock = {getCountryCode:function() {return 'NZ';}}
			, logMock = function() {}
			, windowMock = {wgShowAds: true}
			, documentMock = {}
			, adDecoratorPageDimensionsMock = {isApplicable: function() {return false;}}
			, abTestMock = {inGroup: function() {return false;}}
			, adConfig;

		adConfig = modules['ext.wikia.adEngine.adConfig'](
			logMock,
			windowMock,
			documentMock,
			geoMock,
			abTestMock,
			adDecoratorPageDimensionsMock
			, evolveSlotConfigMock

			// AdProviders
			, adProviderDirectGptMock
			, adProviderLaterMock
			, adProviderNullMock
		);

		expect(adConfig.getProvider(['TOP_LEADERBOARD'])).not.toBe(adProviderLaterMock, 'adProviderLaterMock');
	});

	it('getProvider Null wins over all', function() {
		var adProviderNullMock = {name: 'NullMock'}
			, evolveSlotConfig = {canHandleSlot: function() {return true;}}
			, adProviderDirectGptMock = {name:'GptMock', canHandleSlot: function() {return true}}
			, adProviderLaterMock = {name: 'LaterMock', canHandleSlot: function() {return true}}
			, geoMock = {getCountryCode: function() {return 'hi-value-country'}}
			, logMock = function() {}
			, windowMock = {wgHighValueCountries: {'hi-value-country': true}, wgShowAds: false}
			, documentMock = {}
			, adDecoratorPageDimensionsMock = {isApplicable: function() {return false;}}
			, abTestMock = {inGroup: function() {return false;}}
			, adConfig;

		adConfig = modules['ext.wikia.adEngine.adConfig'](
			logMock,
			windowMock,
			documentMock,
			geoMock,
			abTestMock,
			adDecoratorPageDimensionsMock
			, evolveSlotConfig

			// AdProviders
			, adProviderDirectGptMock
			, adProviderLaterMock
			, adProviderNullMock
		);

		// First check if NullProvider wins over GPT
		expect(adConfig.getProvider(['TOP_LEADERBOARD'])).toBe(adProviderNullMock, 'adProviderNullMock wgShowAds false');

		// Second check if NullProvider wins over Later
		geoMock.getCountryCode = function() {};
		expect(adConfig.getProvider(['foo'])).toBe(adProviderNullMock, 'adProviderNullMock wgShowAds false');

		// Third check if NullProvider wins over Evolve
		geoMock.getCountryCode = function() {return 'NZ'};
		expect(adConfig.getProvider(['TOP_LEADERBOARD'])).toBe(adProviderNullMock, 'adProviderNullMock wgShowAds false');
	});
});
