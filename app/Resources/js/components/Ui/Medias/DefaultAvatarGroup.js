// @flow
import * as React from 'react';
import styled from 'styled-components';
import colors from '../../../utils/colors';

type Props = {
  className?: string,
  size: number,
};

export const Container = styled.svg.attrs({
  className: 'default-avatar-group',
})`
  border-radius: 50%;
  transform: rotateX(180deg);
  color: #fff;
  background-color: ${colors.primaryColor};
`;

export class DefaultAvatarGroup extends React.Component<Props> {
  static defaultProps = {
    size: 45,
  };

  render() {
    const { className, size } = this.props;
    return (
      <Container
        width={`${size}px`}
        height={`${size}px`}
        viewBox="-200 -200 900 900"
        xmlns="http://www.w3.org/2000/svg"
        role="img"
        x="0px"
        y="0px"
        version="1.1"
        className={className}
        enableBackground="new 0 0 500 500">
        <path
          strokeMiterlimit="10"
          strokeLinejoin="round"
          strokeWidth="3"
          stroke="#FFFFFF"
          fill="white"
          d="M455 190c-14 4-31 9-45 15l0 22c9 5 25 17 26 45 8 5 11 17 11 30 0 4 0 11-3 17-1 2-3 4-4 5 5 10 13 25 7 42-7 22-38 30-62 30-21 0-48-6-59-23-11 1-16-4-19-8-8-11-2-28 3-40-2-1-4-4-5-6-3-6-4-13-4-17 0-12 4-25 11-30 2-26 18-39 29-45l0-22c-5-2-12-5-18-7 24-15 35-29 35-42l0-102 145 0c5 0 9 4 9 8l0 97c0 13-16 18-57 31z m-153 2c0 0 0-1-1-1-1 1-2 2-4 3-22 12-50 26-75 36l0 32c1 1 2 1 2 2 1 0 1 0 1 1 6 5 13 15 16 33 0 1 0 2 1 4 0 1 0 3 0 4 0 3 1 5 1 8 1 1 2 1 3 2 0 0 0 0 0 0 4 4 7 10 9 16 0 0 0 1 0 1 1 1 1 2 1 3 0 1 0 1 0 2 0 1 1 2 1 3 0 1 0 3 0 4 0 7-2 13-4 18 0 0 0 0-1 0-1 2-3 4-6 6 1 2 2 4 3 6 1 4 3 8 4 11 0 1 0 1 0 1 1 4 1 7 2 11 0 0 0 0 0 0 0 3 0 7 0 10 0 0 0 1 0 2 0 3-1 6-1 9-1 2-1 4-2 5 0 1-1 2-1 3 0 0-1 1-1 1-1 1-1 3-2 4 0 0 0 0 0 0-4 5-8 9-14 13 0 0 0 0 0 0-2 1-3 2-4 2-11 6-25 10-38 11 0 0 0 0 0 0-2 0-4 0-5 0-2 0-4 0-6 0-4 0-7 0-11 0-1 0-2 0-3-1-2 0-4 0-6 0-2-1-4-1-5-1-2-1-3-1-4-1-2-1-4-1-6-2-2 0-4-1-6-2-1 0-1 0-2-1-11-4-20-11-25-19 0 0 0 0 0-1-1-1-2-2-3-3-1 0-3 0-4 0-2 0-4-1-6-1 0 0-1 0-1 0-2-1-3-1-4-2-1 0-1 0-2-1-1 0-2-1-2-2-1 0-1 0-1-1-1 0-2-1-2-2-1-2-2-3-3-5 0-1 0-1 0-1 0-2 0-3-1-5 0 0 0-1 0-1 0-2 0-3 0-5 0 0 1-1 1-1 0-2 0-4 0-5 0-1 1-1 1-1 0-2 1-4 1-6 0 0 0-1 0-1 1-2 2-4 2-5 0-1 0-1 1-1 1-4 3-8 4-12 0 0 0 0 0 0-2-1-5-4-6-6-3-5-5-11-5-18 0-2 0-3 0-5 0 0 0-1 1-2 0-1 0-1 0-2 0-1 0-2 1-4 0 0 0 0 0 0 2-6 5-12 9-16 0 0 0 0 0 0 1-1 2-1 3-2 0-3 0-6 1-9 0-1 0-2 0-3 4-24 15-35 21-40l0-32c-25-10-53-24-75-36-8-5-15-9-20-12-16-12-24-20-24-26l0-94c0-4 4-8 9-8l332 0 0 102c0 7-12 20-39 36z"
        />
      </Container>
    );
  }
}

export default DefaultAvatarGroup;
