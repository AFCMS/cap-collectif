/* eslint-env jest */
/* @flow */
import React from 'react';
import { shallow } from 'enzyme';
import { EditProfileTabs, getHashKey } from './EditProfileTabs';
import { features } from '../../../redux/modules/default';
import { $fragmentRefs, $refType } from '../../../mocks';

describe('<EditProfileTabs />', () => {
  const globalProps = {
    languageList: [
      { translationKey: 'french', code: 'fr-FR' },
      { translationKey: 'english', code: 'en-GB' },
      { translationKey: 'deutsch', code: 'de-DE' },
    ],
    viewer: {
      $refType,
      $fragmentRefs,
      hasPassword: true,
    },
  };

  const propsWithoutParis = {
    features: {
      ...features,
      profiles: true,
      login_paris: false,
    },
    loginWithOpenId: false,
  };

  const propsWithParisAndNotProfiles = {
    features: {
      ...features,
      profiles: false,
      login_paris: true,
    },
    loginWithOpenId: false,
  };

  const propsWithOpenIdAndNotProfiles = {
    features: {
      ...features,
      profiles: false,
      login_paris: false,
    },
    loginWithOpenId: true,
  };

  it('should render all tabs', () => {
    const wrapper = shallow(<EditProfileTabs {...propsWithoutParis} {...globalProps} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('should render all tabs except profile, password and account (Paris)', () => {
    const wrapper = shallow(<EditProfileTabs {...propsWithParisAndNotProfiles} {...globalProps} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('should render all tabs except profile, password and account (OpenID)', () => {
    const wrapper = shallow(
      <EditProfileTabs {...propsWithOpenIdAndNotProfiles} {...globalProps} />,
    );
    expect(wrapper).toMatchSnapshot();
  });

  it('should return profile from hashkey', () => {
    const result = getHashKey('profile');
    expect(result).toEqual('profile');
  });

  it('should return password from hashkey', () => {
    const result = getHashKey('password');
    expect(result).toEqual('password');
  });

  it('should return personal-data from hashkey', () => {
    const result = getHashKey('personal-data');
    expect(result).toEqual('personal-data');
  });

  it('should return account from hashkey', () => {
    const result = getHashKey('account');
    expect(result).toEqual('account');
  });

  it('should return notifications from hashkey', () => {
    const result = getHashKey('notifications');
    expect(result).toEqual('notifications');
  });

  it('should return followings from hashkey', () => {
    const result = getHashKey('followings');
    expect(result).toEqual('followings');
  });

  it('should return account from empty hashkey', () => {
    const result = getHashKey('');
    expect(result).toEqual('account');
  });
});
