/* eslint-env mocha */
/* eslint no-unused-expressions:0 */
import React from 'react';
import {expect} from 'chai';
import {shallow} from 'enzyme';
import OpinionSource from './OpinionSource';

describe('<OpinionSource />', () => {
  const sourceUserVip = {
    author: {
      vip: true,
    },
  };
  const sourceUserNotVip = {
    author: {
      vip: false,
    },
  };

  it('should render a li bordered even for empty source', () => {
    const wrapper = shallow(<OpinionSource source={{}} />);
    expect(wrapper.find('li.opinion.block--bordered')).to.have.length(1);
  });

  it('should render a li bordered', () => {
    const wrapper = shallow(<OpinionSource source={sourceUserVip} />);
    expect(wrapper.find('li.opinion.block--bordered')).to.have.length(1);
  });

  it('should render a colored li if author is vip', () => {
    const wrapper = shallow(<OpinionSource source={sourceUserVip} />);
    expect(wrapper.find('li.bg-vip')).to.have.length(1);
  });

  it('should render a white li if not vip', () => {
    const wrapper = shallow(<OpinionSource source={sourceUserNotVip} />);
    expect(wrapper.find('li.bg-vip')).to.not.exists;
  });
});
