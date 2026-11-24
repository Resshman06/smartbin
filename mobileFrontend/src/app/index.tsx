import { View, Text, FlatList, StyleSheet } from 'react-native';

const bins = [
  { id: '1', name: 'Block A - Bin 1', fill: 85 },
  { id: '2', name: 'Block A - Bin 2', fill: 40 },
  { id: '3', name: 'Block B - Bin 1', fill: 92 },
  { id: '4', name: 'Block B - Bin 2', fill: 15 },
];

function getColor(fill: number) {
  if (fill >= 80) return '#e53935'; // 红：快满了
  if (fill >= 50) return '#fb8c00'; // 橙：一半以上
  return '#43a047'; // 绿：还很空
}

export default function Index() {
  return (
    <View style={styles.container}>
      <Text style={styles.title}>Bin Status</Text>
      <FlatList
        data={bins}
        keyExtractor={(item) => item.id}
        renderItem={({ item }) => (
          <View style={styles.card}>
            <Text style={styles.name}>{item.name}</Text>
            <Text style={[styles.fill, { color: getColor(item.fill) }]}>
              {item.fill}%
            </Text>
          </View>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, paddingTop: 60, paddingHorizontal: 16, backgroundColor: '#f5f5f5' },
  title: { fontSize: 28, fontWeight: 'bold', marginBottom: 16 },
  card: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#fff',
    padding: 16,
    borderRadius: 12,
    marginBottom: 12,
  },
  name: { fontSize: 16 },
  fill: { fontSize: 20, fontWeight: 'bold' },
});