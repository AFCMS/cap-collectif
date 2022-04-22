// @flow
import React from 'react';
import { FormattedMessage } from 'react-intl';

type Props = {
  ok?: number,
  nok?: number,
  mitige?: number,
  height?: string,
  width?: string,
  top?: number,
  left?: number,
};

class VotePiechart extends React.Component<Props> {
  static defaultProps = {
    ok: 0,
    nok: 0,
    mitige: 0,
    height: '100%',
    width: '100%',
    top: 0,
    left: 0,
  };

  render() {
    const { ok, mitige, nok, left, top, height, width } = this.props;
    // $FlowFixMe
    if (!__SERVER__ && ok + mitige + nok > 0) {
      const Chart = require('react-google-charts').Chart; // eslint-disable-line
      return (
        <div className="opinion__chart" style={{ textAlign: 'center' }}>
          <Chart
            chartType="PieChart"
            data={[
              [{ type: 'string' }, { type: 'number' }],
              [<FormattedMessage id="vote.ok" />, ok],
              [<FormattedMessage id="vote.mitige" />, mitige],
              [<FormattedMessage id="vote.nok" />, nok],
            ]}
            height={height}
            width={width}
            options={{
              legend: 'none',
              chartArea: {
                left,
                top,
                width: '100%',
                height: '85%',
              },
              colors: ['#5cb85c', '#f0ad4e', '#d9534f'],
              pieSliceText: 'value',
              backgroundColor: 'transparent',
            }}
          />
        </div>
      );
    }
    return null;
  }
}

export default VotePiechart;
